<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;


class InvalidRequestException extends Exception
{
    /**
     * InvalidRequestException constructor.
     */
    public function __construct($message, $code = 400, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->getMessage()], $this->getCode());
        }

        return view('pages.error', ['msg' => $this->getMessage(), 'code' => $this->getCode()]);
    }
}