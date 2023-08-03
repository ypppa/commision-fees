<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Unit\Service\Parser;

use Paysera\Component\Normalization\CoreDenormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\ReaderException;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;
use Ypppa\CommissionFees\Service\Parser\CommissionRulesParser;
use Ypppa\CommissionFees\Service\Reader\ReaderInterface;

/**
 * @codeCoverageIgnore
 */
class CommissionRulesParserTest extends TestCase
{
    private ReaderInterface|MockObject $reader;
    private CoreDenormalizer|MockObject $denormalizer;
    private CommissionRulesParser $parser;

    public function setUp(): void
    {
        $this->reader = $this->createMock(ReaderInterface::class);
        $this->denormalizer = $this->createMock(CoreDenormalizer::class);
        $this->parser = new CommissionRulesParser($this->reader, $this->denormalizer);
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
            (new CommissionFeeRule())->setCommission('0.5'),
            (new CommissionFeeRule())->setCommission('0.3'),
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
