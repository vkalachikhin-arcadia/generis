<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA
 *
 */

namespace oat\oatbox\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Controller extends \Module
{
    protected $request;
    protected $response;

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return ServerRequestInterface
     */
    protected function getPsrRequest()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getPsrResponse()
    {
        return $this->response;
    }

    public function getRequestParameters()
    {
        return array_merge(
            (array) $this->getPsrRequest()->getParsedBody(),
            $this->getPsrRequest()->getQueryParams(),
            $this->getPsrRequest()->getAttributes(),
            $this->getHeaders()
        );
    }

    public function hasRequestParameter($name)
    {
        if (!$this->request) {
            $this->maskAsDeprecatedCall(__METHOD__);
            return parent::hasRequestParameter($name);
        }
        return $this->hasHeader($name) || $this->hasGetParameter($name) || $this->hasPostParameter($name);
    }

    public function hasHeader($name)
    {
        if (!$this->request) {
            $this->maskAsDeprecatedCall(__METHOD__);
            return parent::hasHeader($name);
        }
        return $this->getPsrRequest()->hasHeader(strtolower($name));
    }

    public function hasPostParameter($name)
    {
        return in_array($name, array_keys((array) $this->getPsrRequest()->getParsedBody()));
    }

    public function hasGetParameter($name)
    {
        return in_array($name, array_keys((array) $this->getPsrRequest()->getQueryParams()));
    }

    public function getRequestParameter($name)
    {
        if (!$this->request) {
            $this->maskAsDeprecatedCall(__METHOD__);
            return parent::getRequestParameter($name);
        }

        if ($this->hasRequestParameter($name)) {
            return $this->getRequestParameters()[$name];
        } elseif ($this->getPsrRequest()->hasHeader($name)) {
            return $this->getHeaders()[strtolower($name)];
        } else {
            return false;
        }
    }

    public function getHeaders()
    {
        if (!$this->request) {
            $this->maskAsDeprecatedCall(__FUNCTION__);
            return parent::getHeaders();
        }

        $headers = [];
        foreach ($this->getPsrRequest()->getHeaders() as $name => $values) {
//            $values = explode(',', $header);
            $headers[strtolower($name)] = (count($values) == 1) ? reset($values) : $values;
        }
        return $headers;
    }

    public function getHeader($name)
    {
        if (!$this->request) {
            $this->maskAsDeprecatedCall(__FUNCTION__);
            return parent::getHeader($name);
        }
        return $this->getPsrRequest()->getHeader($name);
    }

    public function hasCookie($name)
    {
        return isset($this->getPsrRequest()->getCookieParams()[$name]);
    }

    public function getCookie($name)
    {
        if ($this->hasCookie($name)) {
            return $this->getPsrRequest()->getCookieParams()[$name];
        } else {
            return false;
        }
    }

    public function getRequestMethod()
    {
        return $this->getPsrRequest()->getMethod();
    }

    public function isRequestGet()
    {
        return $this->getRequestMethod() == 'GET';
    }

    public function isRequestPost()
    {
        return $this->getRequestMethod() == 'POST';
    }

    public function isRequestPut()
    {
        return $this->getRequestMethod() == 'PUT';
    }

    public function isRequestDelete()
    {
        return $this->getRequestMethod() == 'DELETE';
    }

    public function isRequestHead()
    {
        return $this->getRequestMethod() == 'HEAD';
    }

    public function getUserAgent()
    {
        return $this->getPsrRequest()->getHeader('user-agent');
    }

    public function getQueryString()
    {
        return $this->getPsrRequest()->getUri()->getQuery();
    }

    public function getRequestURI()
    {
        return $this->getPsrRequest()->getUri()->getPath();
    }

    public function setCookie($name, $value = null, $expire = null, $domainPath = null, $https = null, $httpOnly = null)
    {
        return setcookie($name, $value, $expire, $domainPath, $https, $httpOnly);
    }

    public function setContentHeader($contentType, $charset = 'UTF-8')
    {
        $this->response = $this->getPsrResponse()->withHeader('content-type', $contentType . ';' . $charset);
        return $this;
    }

    public function getContentType()
    {
        return $this->getPsrRequest()->getHeader('content-type');
    }

    protected function maskAsDeprecatedCall($function = null)
    {
        $message = '[DEPRECATED]  Deprecated call ';
        if (!is_null($function)) {
            $message .= 'of "' . $function . '"';
        }
        $message .= ' (' . get_called_class() .')';
        \common_Logger::w($message);
    }
}