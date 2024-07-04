<?php
declare(strict_types=1);
namespace Npostnik\Min\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\StreamFactory;
use WyriHaximus\HtmlCompress\Factory;

/*
 * Thanks to https://www.in2code.de/aktuelles/php-html-output-in-typo3-komprimieren/
 */

/**
 * Class HtmlCompress
 */
class HtmlCompress implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if(isset($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->user['uid'] > 0) {
            return $response;
        }
        if ($this->isTypeNumSet($request) === false) {
            $stream = $response->getBody();
            $stream->rewind();
            $content = $stream->getContents();
            $newBody = (new StreamFactory())->createStream($this->compressHtml($content));
            $response = $response->withBody($newBody);
        }
        return $response;
    }

    /**
     * @param string $html
     * @return string
     */
    protected function compressHtml(string $html): string
    {
        $parser = Factory::construct();
        $html = $parser->compress($html);
        $html = $this->removeComments($html);
        return $html;
    }

    /**
     * Remove all html comments but not "<!--TYPO3SEARCH_begin-->" and "<!--TYPO3SEARCH-end-->"
     * @param string $html
     * @return string
     */
    protected function removeComments(string $html): string
    {
        return preg_replace('/<!--((?!TYPO3SEARCH)[\s\S])*?-->/', '', $html);
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isTypeNumSet(ServerRequestInterface $request): bool
    {
        return $request->getAttribute('routing')->getPageType() > 0;
    }
}
