<?php

namespace NoelDeMartin\LaravelDusk;

use Illuminate\Support\Facades\URL;
use Laravel\Dusk\Browser as DuskBrowser;
use NoelDeMartin\LaravelDusk\Facades\Mocking;
use NoelDeMartin\LaravelDusk\Exceptions\BrowserJavascriptRequestError;

class Browser extends DuskBrowser
{
    /**
     * Mock a facade and return the mock proxy.
     *
     * @param  string   $facade
     * @param  mixed[]  ...$arguments
     * @return \NoelDeMartin\LaravelDusk\MockingProxy
     */
    public function mock(string $facade, ...$arguments)
    {
        // Spread statically registered fakes to server
        if (Mocking::hasFake($facade)) {
            $this->registerFake($facade, Mocking::getFake($facade));
        }

        $this->executeJavascriptRequest(
            'POST',
            '/_dusk-mocking/mock',
            [
                'facade'    => $facade,
                'arguments' => json_encode($arguments),
            ]
        );

        return new MockingProxy($this, $facade);
    }

    /**
     * Alias for mock.
     *
     * @param  string   $facade
     * @param  mixed[]  ...$arguments
     * @return \NoelDeMartin\LaravelDusk\MockingProxy
     */
    public function fake(string $facade, ...$arguments)
    {
        return $this->mock($facade, ...$arguments);
    }

    /**
     * Register fake class.
     *
     * @param  string   $facade
     * @param  string   $fake
     * @return NoelDeMartin\LaravelDusk\Browser
     */
    public function registerFake(string $facade, $fake)
    {
        $this->executeJavascriptRequest(
            'POST',
            '/_dusk-mocking/register',
            [
                'facade' => $facade,
                'fake'   => $fake,
            ]
        );

        return $this;
    }

    /**
     * Execute a javascript request in the browser (only GET and POST methods supported).
     *
     * @param  string   $method
     * @param  array    $url
     * @param  array    $params
     * @param  bool     $useSession
     * @param  bool     $responseType
     * @return array
     */
    public function executeJavascriptRequest($method, $url, $params = [], $useSession = true)
    {
        $this->driver->manage()->timeouts()->setScriptTimeout(1);

        $result = $this->driver->executeAsyncScript(
            'var callback = arguments[0];'.
            'var request = new XMLHttpRequest();'.
            $this->buildJavascriptRequestCredentialsScript($useSession).
            $this->buildJavascriptOpenRequestScript($method, $url, $params).
            'request.onreadystatechange = function() {'.
                'try {'.
                    'if (request.readyState == XMLHttpRequest.DONE) {'.
                        'if (request.getResponseHeader("Content-Type") == "application/json") {'.
                            'callback(JSON.parse(request.responseText));'.
                        '} else {'.
                            'callback(request.responseText);'.
                        '}'.
                    '}'.
                '} catch (e) {'.
                    'callback("error:" + e.message);'.
                '}'.
            '};'.
            $this->buildJavascriptSendRequestScript($method, $params)
        );

        if (is_string($result) && starts_with($result, 'error:')) {
            throw new BrowserJavascriptRequestError(substr($result, 6));
        }

        return $result;
    }

    private function buildJavascriptRequestCredentialsScript($useSession)
    {
        return $useSession ? 'request.withCredentials = true;' : '';
    }

    private function buildJavascriptOpenRequestScript($method, $url, $params)
    {
        switch ($method) {
            case 'GET':
                return 'request.open("GET", "'. rtrim(self::$baseUrl, '/') . $url.'?'.http_build_query($params).'", true);';
            case 'POST':
                return 'request.open("POST", "'. rtrim(self::$baseUrl, '/') . $url.'", true);'.
                    'request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");';
        }
    }

    private function buildJavascriptSendRequestScript($method, $params)
    {
        switch ($method) {
            case 'GET':
                return 'request.send();';
            case 'POST':
                $token = $this->visit(URL::to(rtrim(self::$baseUrl, '/') . '/_dusk-mocking/csrf_token'))->driver->getPageSource();
                $token = json_decode(strip_tags($token));

                return 'request.send("'.http_build_query(array_merge($params, ['_token' => $token])).'");';
        }
    }
}
