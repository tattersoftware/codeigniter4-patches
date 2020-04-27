<?php namespace Tatter\Patches\Exceptions;

class UpdateException extends \RuntimeException implements ExceptionInterface
{
    public static function forComposerFailure(int $code)
    {
        return new self(lang('Patches.composerFailure', [$code]));
    }
}
