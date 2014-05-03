<?php

class LibCurl
{
    protected static $error;

    /**
     *
     * @param string $url
     * @return str/boolean
     */
    public static function getContent($url, $request_body = '', $request_method = 'GET', $useProxy = false, $httpHeader = array(), $gzip = false, $timeout = 30)
    {
        Trace::debug('=== Start curl ===');

        $retry = 3;

        $startTime = microtime(true);
        do {
            if(!$url)
            {
                Trace::debug('url is empty, skip curl.');
                $response = false;
                break;
            }

            Trace::debug('url: '.$url);
            Trace::verbose('get url: '.$url);
            Trace::debug('method: '.$request_method);
            Trace::debug('header: '.http_build_query($httpHeader));
            Trace::debug('request_body: '.serialize($request_body));
            Trace::debug('timeout: '.$timeout);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            // curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            if($httpHeader)  curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            if($gzip)
            {
                curl_setopt($ch,CURLOPT_ENCODING ,'gzip');
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
            if($useProxy)
            {
                $config=Config::runtimeConfigForKeyPath('proxy');
                if($config)
                {
                    curl_setopt($ch, CURLOPT_PROXY,$config['host'].":".$config['port'] );
                    if (isset($config['username']) && isset($config['password'])) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $config['username'].':'.$config['password']);
                    }
                }
            }
            $response = curl_exec($ch);

            if (false === $response) {
                self::$error = MResult::result(curl_errno($ch), curl_error($ch));
                Trace::debug('curl error: '.curl_error($ch));
                Trace::verbose('curl error: '.curl_error($ch));
                Trace::debug('retry: '.$retry);
                $retry--;
            } else {
                self::$error = MResult::result(MResult::SUCCESS);
                Trace::debug('response length: '.strlen($response));
                Trace::verbose('response: '.var_export($response, true));
                $retry = 0;
            }

            curl_close($ch);

        } while ($retry > 0);

        $timespan = microtime(true) - $startTime;
        Trace::debug('timespan: '.$timespan);
        Trace::debug('=== End curl ===');

        return $response;
    }
    /**
     * @return MResult
     */
    public static function lastError() {
        return self::$error;
    }

}
