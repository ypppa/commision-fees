<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Tests\Unit\Service\Manager;

use DateTimeImmutable;
use Evp\Component\Money\Money;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Ypppa\CommissionFees\Exception\CommissionFeeCalculationFailedException;
use Ypppa\CommissionFees\Exception\UnsupportedCurrencyException;
use Ypppa\CommissionFees\Exception\ValidationException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Operation\OperationDto;
use Ypppa\CommissionFees\Service\Manager\OperationManager;

/**
 * @codeCoverageIgnore
 */
class OperationManagerTest extends TestCase
{
    private OperationManager $manager;
    private ValidatorInterface|MockObject $validator;

    public function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->manager = new OperationManager($this->validator);
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param OperationDto $dto
     * @param Operation    $expectedResult
     *
     * @return void
     * @throws Exception
     */
    public function testCreateFromDto(OperationDto $dto, Operation $expectedResult): void
    {
        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList())
        ;
        $this->assertEquals($expectedResult, $this->manager->createFromDto($dto));
    }

    public function createDataProvider(): array
    {
        return [
            'should return instance of Operation' => [
                'dto' => new OperationDto(
                    '2014-12-31',
                    '1',
                    'private',
                    'withdraw',
                    '1200.00',
                    'EUR'
                ),
                'expectedResult' => new Operation(
                    new DateTimeImmutable('2014-12-31'),
                    '1',
                    'private',
                    'withdraw',
                    new Money('1200.00', 'EUR')
                ),
            ],
        ];
    }

    public function testValidationException(): void
    {
        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList([$this->createMock(ConstraintViolation::class)]))
        ;
        $this->expectException(ValidationException::class);
        $this->manager->createFromDto(new OperationDto('', '', '', '', '1000', 'EUR'));
    }

    /**
     * @dataProvider moneyExceptionProvider
     *
     * @param string       $exceptionClass
     * @param OperationDto $dto
     *
     * @return void
     * @throws Exception
     */
    public function testMoneyException(string $exceptionClass, OperationDto $dto): void
    {
        $this->expectException($exceptionClass);
        $this->manager->createFromDto($dto);
    }

    public function moneyExceptionProvider(): array
    {
        return [
            [
                'exceptionClass' => UnsupportedCurrencyException::class,
                'dto' => new OperationDto('', '', '', '', '1000', 'XXX'),
            ],
            [
                'exceptionClass' => CommissionFeeCalculationFailedException::class,
                'dto' => new OperationDto('', '', '', '', '-', 'EUR'),
            ],
        ];
    }
}
