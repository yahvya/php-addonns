<?php

namespace Tests\AttemptLimiter;

use PhpAddons\AttemptLimiter\AttemptLimiter;
use PhpAddons\AttemptLimiter\AttemptLimiterException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Throwable;

require_once(__DIR__ . "/../../vendor/autoload.php");

class AttemptLimiterTest extends TestCase {
    #[TestDox(text: "Blocage de tentatives")]
    #[DataProvider(methodName: "getFirstCallableTests")]
    #[DataProvider(methodName: "getSecondCallableTests")]
    #[DataProvider(methodName: "getResetTests")]
    /**
     * @brief Test le blocage de tentatives
     * @param array $callable callable
     * @param array $args arguments du callable
     * @param bool $exceptLock si un blocage est attendu
     * @param int $waitTime temps d'attente avant le prochain test
     * @throws AttemptLimiterException en cas d'erreur
     */
    public function testAttempt(array $callable, array $args,bool $exceptLock,int $waitTime = 0):void{
        if($exceptLock)
            $this->expectException(exception: AttemptLimiterException::class);
        else
            $this->assertTrue(condition: true);

        try{
            AttemptLimiter::attempt(callable: $callable,args: $args);
        }
        catch(AttemptLimiterException $e){
            sleep(seconds: $waitTime);
            throw $e;
        }
        catch(Throwable){}
    }

    /**
     * @brief Test pour retour booléen
     * @return array[] données de test
     */
    public static function getFirstCallableTests():array{
        return [
            "(First callable) Non bloquant" => [[CallableClass::class,"firstCallable"],[true],false],
            "(First callable) Pour blocage 1" => [[CallableClass::class,"firstCallable"],[false],false],
            "(First callable) Pour blocage 2" => [[CallableClass::class,"firstCallable"],[false],false],
            "(First callable) Bloquant" => [[CallableClass::class,"firstCallable"],[true],true],
            "(First callable) Bloquant avant temps de validation" => [[CallableClass::class,"firstCallable"],[true],true,2],
            "(First callable) Valide après déblocage" => [[CallableClass::class,"firstCallable"],[true],false]
        ];
    }

    /**
     * @brief Test le renvoi d'exceptions
     * @return array[] données de test
     */
    public static function getSecondCallableTests():array{
        return [
            "(Second callable) Non bloquant" => [[CallableClass::class,"secondCallable"],[false],false],
            "(Second callable) Pour blocage 1" => [[CallableClass::class,"secondCallable"],[true],false],
            "(Second callable) Pour blocage 2" => [[CallableClass::class,"secondCallable"],[true],false],
            "(Second callable) Pour blocage 3" => [[CallableClass::class,"secondCallable"],[true],false],
            "(Second callable) Pour blocage 4" => [[CallableClass::class,"secondCallable"],[true],false],
            "(Second callable) Bloquant" => [[CallableClass::class,"secondCallable"],[false],true],
            "(Second callable) Bloquant avant temps de validation" => [[CallableClass::class,"secondCallable"],[false],true,2],
            "(Second callable) Valide après déblocage" => [[CallableClass::class,"secondCallable"],[false],false]
        ];
    }

    /**
     * @brief Test le reset de blocage
     * @return array[] données de test
     */
    public static function getResetTests():array{
        return [
            "(Reset) Pour blocage 1" => [[CallableClass::class,"secondCallable"],[true],false],
            "(Reset) Pour blocage 2" => [[CallableClass::class,"secondCallable"],[true],false],
            "(Reset) Pour blocage 3" => [[CallableClass::class,"secondCallable"],[true],false],
            "(Reset) Reset" => [[CallableClass::class,"secondCallable"],[false],false],
            "(Reset) Valide après reset" => [[CallableClass::class,"secondCallable"],[false],false]
        ];
    }
}
