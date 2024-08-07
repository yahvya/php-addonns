<?php

namespace Test\PhpAddons\AttemptLimiter;

use Exception;
use PhpAddons\AttemptLimiter\AttemptErrorMarker;
use PhpAddons\AttemptLimiter\AttemptLimitable;

class CallableClass{
    #[AttemptLimitable(
        countOfAttempt: 2,
        timeBeforeNextAttempt: 5,
        errorMarker: AttemptErrorMarker::FALSE_RETURNED,
        resetOnSuccess: true
    )]
    public static function firstCallable(bool $toReturn):bool{
        return $toReturn;
    }

    #[AttemptLimitable(
        countOfAttempt: 4,
        timeBeforeNextAttempt: 5,
        errorMarker: AttemptErrorMarker::EXCEPTION_THROWN,
        resetOnSuccess: true
    )]
    /**
     * @throws Exception
     */
    public static function secondCallable(bool $throw):void{
        if($throw)
            throw new Exception(message: "test");
    }
}