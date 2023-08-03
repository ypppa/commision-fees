<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Unit\Service\Parser;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Paysera\Component\Normalization\CoreDenormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\ReaderException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Service\Parser\OperationsParser;
use Ypppa\CommissionFees\Service\Reader\ReaderInterface;

/**
 * @codeCoverageIgnore
 */
class OperationsParserTest extends TestCase
{
    private ReaderInterface|MockObject $reader;
    private CoreDenormalizer|MockObject $denormalizer;
    private OperationsParser $parser;

    public function setUp(): void
    {
        $this->reader = $this->createMock(ReaderInterface::class);
        $this->denormalizer = $this->createMock(CoreDenormalizer::class);
        $this->parser = new OperationsParser($this->reader, $this->denormalizer);
    }

    public function testParse(): void
    {
        $this->reader
            ->method('read')
            ->willReturnCallBack(fn() => yield from array_fill(0, 2, null))
        ;
        $this->denormalizer
            ->method('denormalize')
            ->willReturn(... $this->getDenormalizedResult())
        ;
        $this->assertEquals($this->getDenormalizedResult(), iterator_to_array($this->parser->parse('')));
    }

    private function getDenormalizedResult(): array
    {
        return [
            new Operation(
                new DateTimeImmutable('2014-12-31'),
                '4',
                'private',
                'withdraw',
                new Money('1200.00', 'EUR')
            ),
            new Operation(
                new DateTimeImmutable('2015-01-01'),
                '1',
                'business',
                'deposit',
                new Money('1000.00', 'EUR')
            ),
        ];
    }

    public function testException(): void
    {
        $this->reader->method('read')
            ->willThrowException(new ReaderException())
        ;
        $this->expectException(CommissionFeeCalculationFailedException::class);
        $generator = $this->parser->parse('');
        $generator->next();
    }
}
