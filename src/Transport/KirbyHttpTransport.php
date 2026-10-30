<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Transport;

final class KirbyHttpTransport implements HttpTransport
{
    public function send(string $method, string $url, string $body, array $headers): HttpResponse
    {
        $curl = curl_init($url);
        if ($curl === false) {
            throw new \RuntimeException('Unable to initialise HTTP transport');
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . str_replace(["\r", "\n"], '', $value);
        }
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        if ($body !== '') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        $response = curl_exec($curl);
        if (!is_string($response)) {
            $message = curl_error($curl);
            curl_close($curl);
            throw new \RuntimeException($message === '' ? 'HTTP transport failed' : $message);
        }
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        return new HttpResponse($status, $response);
    }
}
