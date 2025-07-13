<?php

namespace App\Client;

use App\Client\Exception\AuthenticationException;
use App\Client\Exception\CrmApiException;
use App\Client\Exception\NetworkException;
use App\Client\Exception\NotFoundException;
use App\Client\Exception\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CrmApiClient
{
    private Client $httpClient;
    private string $baseUrl;
    private string $apiToken;
    private int $timeout;
    private int $retryAttempts;
    private int $retryDelay;

    public function __construct(
        string $baseUrl,
        string $apiToken,
        int $timeout = 30,
        int $retryAttempts = 3,
        int $retryDelay = 1000
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiToken = $apiToken;
        $this->timeout = $timeout;
        $this->retryAttempts = $retryAttempts;
        $this->retryDelay = $retryDelay;

        $this->httpClient = $this->createHttpClient();
    }

    private function createHttpClient(): Client
    {
        $stack = HandlerStack::create();

        $stack->push(Middleware::retry(
            $this->getRetryDecider(),
            $this->getRetryDelay()
        ));


        return new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'handler' => $stack,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'CRM-API-Client/1.0'
            ]
        ]);
    }

    private function getRetryDecider(): callable
    {
        return function (
            int $retries,
            RequestInterface $request,
            ?ResponseInterface $response = null,
            ?RequestException $exception = null
        ): bool {
            if ($retries >= $this->retryAttempts) {
                return false;
            }

            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response && $response->getStatusCode() >= 500) {
                return true;
            }

            if ($response && $response->getStatusCode() === 429) {
                return true;
            }

            return false;
        };
    }

    private function getRetryDelay(): callable
    {
        return function (int $retries): int {
            return $this->retryDelay * $retries;
        };
    }

    private function makeRequest(string $method, string $endpoint, array $options = [])
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            $body = $response->getBody()->getContents();

            return json_decode($body, true) ?? [];

        } catch (ClientException $e) {
            $this->handleClientException($e, $method, $endpoint);
        } catch (ServerException $e) {
            $this->handleServerException($e, $method, $endpoint);
        } catch (ConnectException $e) {
            $this->handleNetworkException($e, $method, $endpoint);
        } catch (TransferException $e) {
            $this->handleTransferException($e, $method, $endpoint);
        }
    }

    private function handleClientException(ClientException $e, string $method, string $endpoint): never
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true) ?? [];

        switch ($statusCode) {
            case 400:
                throw new ValidationException(
                    message: $data['message'] ?? 'Validation failed',
                    validationErrors: $data['fields'] ?? [],
                    httpMethod: $method,
                    endpoint: $endpoint,
                    httpStatusCode: $statusCode,
                    context: $data
                );

            case 401:
                throw new AuthenticationException(
                    message: $data['message'] ?? 'Authentication failed',
                    httpMethod: $method,
                    endpoint: $endpoint,
                    httpStatusCode: $statusCode,
                    context: $data
                );

            case 403:
                throw new AuthenticationException(
                    message: $data['message'] ?? 'Access denied',
                    httpMethod: $method,
                    endpoint: $endpoint,
                    httpStatusCode: $statusCode,
                    context: $data
                );

            case 404:
                throw new NotFoundException(
                    message: $data['message'] ?? 'Resource not found',
                    httpMethod: $method,
                    endpoint: $endpoint,
                    httpStatusCode: $statusCode,
                    context: $data
                );

            default:
                throw new CrmApiException(
                    message: $data['message'] ?? 'API request failed',
                    code: $statusCode,
                    httpMethod: $method,
                    endpoint: $endpoint,
                    httpStatusCode: $statusCode,
                    context: $data
                );
        }
    }

    private function handleServerException(ServerException $e, string $method, string $endpoint): void
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true) ?? [];

        throw new CrmApiException(
            message: $data['message'] ?? 'Server error occurred',
            code: $statusCode,
            httpMethod: $method,
            endpoint: $endpoint,
            httpStatusCode: $statusCode,
            context: $data
        );
    }

    private function handleNetworkException(ConnectException $e, string $method, string $endpoint): void
    {
        throw new NetworkException(
            message: $e->getMessage(),
            httpMethod: $method,
            endpoint: $endpoint,
            context: ['original_error' => $e->getMessage()]
        );
    }

    private function handleTransferException(TransferException $e, string $method, string $endpoint): void
    {
        throw new CrmApiException(
            message: $e->getMessage(),
            httpMethod: $method,
            endpoint: $endpoint,
            context: ['original_error' => $e->getMessage()]
        );
    }

    public function createSubscriber(array $subscriberData): array
    {
        $data = $this->makeRequest('POST', '/api/subscriber', [
            'json' => [
                'emailAddress' => $subscriberData['emailAddress'] ?? '',
                'firstName' => $subscriberData['firstName'] ?? null,
                'lastName' => $subscriberData['lastName'] ?? null,
                'dateOfBirth' => $subscriberData['dateOfBirth'] ?? null,
                'marketingConsent' => $subscriberData['marketingConsent'] ?? false,
                'lists' => $subscriberData['listIds'] ?? []
            ]
        ]);

        return $data['subscriber'] ?? [];
    }

    public function createOrUpdateSubscriber(array $subscriberData): array
    {
        $data = $this->makeRequest('PUT', '/api/subscriber', [
            'json' => [
                'emailAddress' => $subscriberData['emailAddress'] ?? '',
                'firstName' => $subscriberData['firstName'] ?? null,
                'lastName' => $subscriberData['lastName'] ?? null,
                'dateOfBirth' => $subscriberData['dateOfBirth'] ?? null,
                'marketingConsent' => $subscriberData['marketingConsent'] ?? false,
                'lists' => $subscriberData['listIds'] ?? []
            ]
        ]);

        return $data['subscriber'] ?? [];
    }

    public function getSubscriberLists(): array
    {
        $data = $this->makeRequest('GET', '/api/lists');

        return $data['lists'] ?? [];
    }

    public function getRawSubscriberLists(): array
    {
        return $this->makeRequest('GET', '/api/lists');
    }

    public function createEnquiry(string $subscriberId, array $enquiryData): array
    {
        $data = $this->makeRequest('POST', "/api/subscriber/{$subscriberId}/enquiry", [
            'json' => [
                'message' => $enquiryData['message'] ?? ''
            ]
        ]);

        return $data['enquiry'] ?? [];
    }

    public function getApiStatus(): array
    {
        return $this->makeRequest('GET', '/');
    }
}
