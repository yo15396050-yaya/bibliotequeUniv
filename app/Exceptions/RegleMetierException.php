<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception métier : son message est destiné à l'utilisateur final
 * (« Cet exemplaire est déjà emprunté. »), jamais une trace technique.
 */
class RegleMetierException extends Exception
{
    /** @var array<int, string> */
    protected array $motifs = [];

    /**
     * @param  array<int, string>  $motifs
     */
    public static function avec(array $motifs, ?string $entete = null): self
    {
        $message = $entete
            ? $entete.' '.implode(' ', $motifs)
            : implode(' ', $motifs);

        $exception = new self($message);
        $exception->motifs = $motifs;

        return $exception;
    }

    /** @return array<int, string> */
    public function motifs(): array
    {
        return $this->motifs ?: [$this->getMessage()];
    }
}
