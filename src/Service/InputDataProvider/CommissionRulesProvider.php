<?php

declare(strict_types=1);

namespace Ypppa\CommissionFees\Service\InputDataProvider;

use Throwable;
use Ypppa\CommissionFees\Exception\CommissionRulesLoadException;
use Ypppa\CommissionFees\Model\Operation\Operation;
use Ypppa\CommissionFees\Model\Rule\CommissionFeeRule;
use Ypppa\CommissionFees\Service\Parser\ParserInterface;

class CommissionRulesProvider implements CommissionRulesProviderInterface
{
    private ParserInterface $parser;
    private string $filePath;
    /**
     * @var CommissionFeeRule[]
     */
    private ?array $rules;

    public function __construct(ParserInterface $parser, string $filePath)
    {
        $this->parser = $parser;
        $this->filePath = $filePath;
        $this->rules = null;
    }

    public function getRule(Operation $operation): CommissionFeeRule
    {
        if ($this->rules === null) {
            $this->load();
        }

        $matchedRules = $this->getMatchedRules($operation);

        if (count($matchedRules) > 0) {
            $matchedRules = $this->sort($matchedRules);

            return $matchedRules[0];
        }

        return new CommissionFeeRule();
    }

    /**
     * @return void
     * @throws CommissionRulesLoadException
     */
    private function load(): void
    {
        try {
            $this->rules = iterator_to_array($this->parser->parse($this->filePath, CommissionFeeRule::class));
        } catch (Throwable $exception) {
            throw new CommissionRulesLoadException($exception);
        }
    }

    /**
     * @param Operation $operation
     *
     * @return CommissionFeeRule[]
     */
    private function getMatchedRules(Operation $operation): array
    {
        $matchedRules = [];
        foreach ($this->rules as $rule) {
            if (
                (empty($rule->getUserId()) || in_array($operation->getUserId(), $rule->getUserId()))
                && ($rule->getUserType() === null || $operation->getUserType() === $rule->getUserType())
                && ($rule->getOperationType() === null || $operation->getOperationType() === $rule->getOperationType())
            ) {
                $matchedRules[] = $rule;
            }
        }

        return $matchedRules;
    }

    /**
     * @param CommissionFeeRule[] $rules
     *
     * @return CommissionFeeRule[]
     */
    private function sort(array $rules): array
    {
        usort($rules, function (CommissionFeeRule $rule1, CommissionFeeRule $rule2): int {
            if (!empty($rule1->getUserId()) && empty($rule2->getUserId())) {
                return -1;
            }
            if (empty($rule1->getUserId()) && !empty($rule2->getUserId())) {
                return 1;
            }
            if ($rule1->getUserType() !== null && $rule2->getUserType() === null) {
                return -1;
            }
            if ($rule1->getUserType() === null && $rule2->getUserType() !== null) {
                return 1;
            }
            if ($rule1->getOperationType() !== null && $rule2->getOperationType() === null) {
                return -1;
            }
            if ($rule1->getOperationType() === null && $rule2->getOperationType() !== null) {
                return 1;
            }

            return 0;
        });

        return $rules;
    }

}
