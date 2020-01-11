<?php

namespace App\Exceptions;

use Exception;

class InternalException extends Exception
{
    /**
     * @var string
     */
    protected $msgForUser;

    /**
     * InternalException constructor.
     *
     * @param string          $realMsg
     * @param string          $msgForUser   给用户的错误提示
     * @param int             $code
     * @param \Throwable|null $previous
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
            return response()->json(['message' => $this->msgForUser], $this->getCode());
        }

        return view('pages.error', ['message' => $this->msgForUser, 'code' => $this->getCode()]);
    }
}
