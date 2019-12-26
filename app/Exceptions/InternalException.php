<?php

namespace App\Exceptions;

use Exception;

class InternalException extends Exception
{
    protected $msgForUser;

    /**
     * InvalidRequestException constructor.
     */
    public function __construct($realMsg, $msgForUser = '系统内部错误', $code = 500, \Throwable $previous = null)
    {
        $this->msgForUser = $msgForUser;
        parent::__construct($realMsg, $code, $previous);
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
            return response()->json(['msg' => $this->msgForUser], $this->getCode());
        }

        return view('pages.error', ['msg' => $this->msgForUser, 'code' => $this->getCode()]);
    }
}
