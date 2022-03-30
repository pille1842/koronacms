<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
        $this->urlGenerator = $urlGenerator;
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        if (
            $request->isXmlHttpRequest() ||
            false === array_search('text/html', $request->getAcceptableContentTypes())
        ) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Unauthorized.');
        }

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }
}
