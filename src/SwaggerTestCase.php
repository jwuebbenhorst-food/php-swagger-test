<?php
/**
 * User: jg
 * Date: 22/05/17
 * Time: 15:32
 */

namespace ByJG\Swagger;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class SwaggerTestCase extends WebTestCase
{
    /**
     * @var \ByJG\Swagger\SwaggerSchema
     */
    protected $swaggerSchema;

    protected $filePath;

    protected function setUp()
    {
        if (empty($this->filePath)) {
            throw new \Exception('You have to define the property $filePath');
        }

        $this->swaggerSchema = new SwaggerSchema(file_get_contents($this->filePath));
    }

    protected function getCustomHeader()
    {
        return [];
    }

    /**
     * @param string $method The HTTP Method: GET, PUT, DELETE, POST, etc
     * @param string $path The REST path call
     * @param int $statusExpected
     * @param array|null $query
     * @param array|null $requestBody
     * @return mixed
     */
    protected function makeRequest($method, $path, $statusExpected = 200, array $query = null, $requestBody = null)
    {
        $client = static::createClient();

        // Preparing Header
        $header = array_merge([
                'Accept' => 'application/json'
            ],
            $this->getCustomHeader()
        );

        // Defining Variables
        $basePath = $this->swaggerSchema->getBasePath();

        // Check if the body is the expected before request
        $bodyRequestDef = $this->swaggerSchema->getRequestParameters("$basePath$path", $method);
        $bodyRequestDef->match($requestBody);

        $client->request(
            $method,
            "$basePath$path",
            $query ?? [],
            [],
            $header,
            json_encode($requestBody)
        );

        $responseBody = json_decode($client->getResponse()->getContent(), true);
        $statusReturned = $client->getResponse()->getStatusCode();

        // Assert results
        $this->assertEquals($statusExpected, $statusReturned, json_encode($responseBody, JSON_PRETTY_PRINT));

        $bodyResponseDef = $this->swaggerSchema->getResponseParameters("$basePath$path", $method, $statusExpected);
        $bodyResponseDef->match($responseBody);

        return $responseBody;
    }
}
