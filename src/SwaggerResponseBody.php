<?php
/**
 * User: jg
 * Date: 22/05/17
 * Time: 10:52
 */

namespace ByJG\Swagger;

use ByJG\Swagger\Exception\NotMatchedException;

class SwaggerResponseBody extends SwaggerBody
{
    public function match($body)
    {
        if (!isset($this->structure['schema']) && !isset($this->structure['$ref'])) {
            if (!empty($body)) {
                throw new NotMatchedException("Expected empty body for " . $this->name);
            }
            return true;
        }

        if (isset($this->structure['$ref'])) {
            $defintion = $this->swaggerSchema->getDefintion($this->structure['$ref']);
            return $this->matchSchema($this->structure['$ref'], $defintion['schema'], $body);
        }

        return $this->matchSchema($this->name, $this->structure['schema'], $body);
    }
}
