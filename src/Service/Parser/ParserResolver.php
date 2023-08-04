<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\Parser;

use Traversable;

class ParserResolver
{
    private iterable $parsers;

    public function __construct(iterable $parsers)
    {
        $this->parsers = $parsers instanceof Traversable ? iterator_to_array($parsers) : $parsers;
    }

    public function getParser(string $key): ?ParserInterface
    {
        return $this->parsers[$key] ?? null;
    }
}
