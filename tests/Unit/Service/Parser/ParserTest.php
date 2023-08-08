<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Unit\Service\Parser;

use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\DenormalizationContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\ReaderException;
use Ypppa\CommissionFees\Model\Operation\OperationDto;
use Ypppa\CommissionFees\Service\Parser\Parser;
use Ypppa\CommissionFees\Service\Reader\ReaderInterface;

/**
 * @codeCoverageIgnore
 */
class ParserTest extends TestCase
{
    private ReaderInterface|MockObject $reader;
    private Parser $parser;
    private ValidatorInterface|MockObject $validator;
    private CoreDenormalizer|MockObject $denormalizer;

    public function setUp(): void
    {
        $this->reader = $this->createMock(ReaderInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->denormalizer = $this->createMock(CoreDenormalizer::class);
        $this->parser = new Parser(
            $this->reader,
            $this->validator,
            $this->denormalizer,
            $this->createMock(DenormalizationContext::class)
        );
    }

    public function testParse(): void
    {
        $this->reader
            ->method('read')
            ->willReturnCallBack(fn() => yield from array_fill(0, 2, null))
        ;
        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList())
        ;
        $this->denormalizer
            ->method('denormalize')
            ->willReturn(... $this->getDenormalizedResult())
        ;
        $this->assertEquals(
            $this->getDenormalizedResult(),
            iterator_to_array($this->parser->parse('', OperationDto::class))
        );
    }

    private function getDenormalizedResult(): array
    {
        return [
            new OperationDto(
                '2014-12-31',
                '4',
                'private',
                'withdraw',
                '1200.00',
                'EUR'
            ),
            new OperationDto(
                '2015-01-01',
                '1',
                'business',
                'deposit',
                '1000.00',
                'EUR'
            ),
        ];
    }

    public function testException(): void
    {
        $this->reader->method('read')
            ->willThrowException(new ReaderException())
        ;
        $this->expectException(CommissionFeeCalculationFailedException::class);
        $generator = $this->parser->parse('', OperationDto::class);
        $generator->next();
    }
}
