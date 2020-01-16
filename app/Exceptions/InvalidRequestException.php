<?php

namespace App\Exceptions;

use Exception;

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
            return response()->json(['message' => $this->getMessage()], $this->getCode());
        }

        return view('pages.error', ['message' => $this->getMessage(), 'code' => $this->getCode()]);
    }
}
