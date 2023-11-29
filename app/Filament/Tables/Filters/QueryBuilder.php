<?php

/*
<COPYRIGHT>

    Copyright © 2022-2023, Canyon GBS LLC. All rights reserved.

    Advising App™ is licensed under the Elastic License 2.0. For more details,
    see https://github.com/canyongbs/advisingapp/blob/main/LICENSE.

    Notice:

    - You may not provide the software to third parties as a hosted or managed
      service, where the service provides users with access to any substantial set of
      the features or functionality of the software.
    - You may not move, change, disable, or circumvent the license key functionality
      in the software, and you may not remove or obscure any functionality in the
      software that is protected by the license key.
    - You may not alter, remove, or obscure any licensing, copyright, or other notices
      of the licensor in the software. Any use of the licensor’s trademarks is subject
      to applicable law.
    - Canyon GBS LLC respects the intellectual property rights of others and expects the
      same in return. Canyon GBS™ and Advising App™ are registered trademarks of
      Canyon GBS LLC, and we are committed to enforcing and protecting our trademarks
      vigorously.
    - The software solution, including services, infrastructure, and code, is offered as a
      Software as a Service (SaaS) by Canyon GBS LLC.
    - Use of this software implies agreement to the license terms and conditions as stated
      in the Elastic License 2.0.

    For more information or inquiries please visit our website at
    https://www.canyongbs.com or contact us via email at legal@canyongbs.com.

</COPYRIGHT>
*/

namespace App\Filament\Tables\Filters;

use Closure;
use Exception;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\BaseFilter;
use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use App\Filament\Tables\Filters\QueryBuilder\Concerns\HasConstraints;
use App\Filament\Tables\Filters\QueryBuilder\Forms\Components\RuleBuilder;

class QueryBuilder extends BaseFilter
{
    use HasConstraints;

    /**
     * @var array<string, int | string | null> | null
     */
    protected ?array $constraintPickerColumns = [];

    protected string | Closure | null $constraintPickerWidth = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-tables::filters/query-builder.label'));

        $this->form(fn (QueryBuilder $filter): array => [
            RuleBuilder::make('rules')
                ->label($filter->getLabel())
                ->constraints($filter->getConstraints())
                ->blockPickerColumns($filter->getConstraintPickerColumns())
                ->blockPickerWidth($filter->getConstraintPickerWidth()),
        ]);

        $this->query(function (Builder $query, array $data) {
            $this->applyRulesToQuery($query, $data['rules'], $this->getRuleBuilder());
        });

        $this->baseQuery(function (Builder $query, array $data) {
            $this->applyRulesToBaseQuery($query, $data['rules'], $this->getRuleBuilder());
        });

        $this->columnSpanFull();
    }

    public static function getDefaultName(): ?string
    {
        return 'queryBuilder';
    }

    /**
     * @param  array<string, mixed>  $rules
     */
    public function applyRulesToQuery(Builder $query, array $rules, RuleBuilder $ruleBuilder): Builder
    {
        foreach ($rules as $ruleIndex => $rule) {
            $ruleBuilderBlockContainer = $ruleBuilder->getChildComponentContainer($ruleIndex);

            if ($rule['type'] === RuleBuilder::OR_BLOCK_NAME) {
                $query->where(function (Builder $query) use ($rule, $ruleBuilderBlockContainer) {
                    $isFirst = true;

                    foreach ($rule['data'][RuleBuilder::OR_BLOCK_GROUPS_REPEATER_NAME] as $orGroupIndex => $orGroup) {
                        $query->{$isFirst ? 'where' : 'orWhere'}(function (Builder $query) use ($orGroup, $orGroupIndex, $ruleBuilderBlockContainer) {
                            $this->applyRulesToQuery(
                                $query,
                                $orGroup['rules'],
                                $this->getNestedRuleBuilder($ruleBuilderBlockContainer, $orGroupIndex),
                            );
                        });

                        $isFirst = false;
                    }
                });

                continue;
            }

            $this->tapOperatorFromRule(
                $rule,
                $ruleBuilderBlockContainer,
                fn ($operator) => $operator->applyToBaseQuery($query),
            );
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $rules
     */
    public function applyRulesToBaseQuery(Builder $query, array $rules, RuleBuilder $ruleBuilder): Builder
    {
        foreach ($rules as $ruleIndex => $rule) {
            $ruleBuilderBlockContainer = $ruleBuilder->getChildComponentContainer($ruleIndex);

            if ($rule['type'] === RuleBuilder::OR_BLOCK_NAME) {
                foreach ($rule['data'][RuleBuilder::OR_BLOCK_GROUPS_REPEATER_NAME] as $orGroupIndex => $orGroup) {
                    $this->applyRulesToBaseQuery(
                        $query,
                        $orGroup['rules'],
                        $this->getNestedRuleBuilder($ruleBuilderBlockContainer, $orGroupIndex),
                    );
                }

                continue;
            }

            $this->tapOperatorFromRule(
                $rule,
                $ruleBuilderBlockContainer,
                fn ($operator) => $operator->applyToBaseFilterQuery($query),
            );
        }

        return $query;
    }

    /**
     * @param  array<string, int | string | null> | int | string | null  $columns
     */
    public function constraintPickerColumns(array | int | string | null $columns = 2): static
    {
        if (! is_array($columns)) {
            $columns = [
                'lg' => $columns,
            ];
        }

        $this->constraintPickerColumns = [
            ...($this->constraintPickerColumns ?? []),
            ...$columns,
        ];

        return $this;
    }

    /**
     * @return array<string, int | string | null> | int | string | null
     */
    public function getConstraintPickerColumns(?string $breakpoint = null): array | int | string | null
    {
        $columns = $this->constraintPickerColumns ?? [
            'default' => 1,
            'sm' => null,
            'md' => null,
            'lg' => null,
            'xl' => null,
            '2xl' => null,
        ];

        if ($breakpoint !== null) {
            return $columns[$breakpoint] ?? null;
        }

        return $columns;
    }

    public function constraintPickerWidth(string | Closure | null $width): static
    {
        $this->constraintPickerWidth = $width;

        return $this;
    }

    public function getConstraintPickerWidth(): ?string
    {
        return $this->evaluate($this->constraintPickerWidth);
    }

    protected function getRuleBuilder(): RuleBuilder
    {
        $builder = $this->getForm()->getComponent(fn (Component $component): bool => $component instanceof RuleBuilder);

        if (! ($builder instanceof RuleBuilder)) {
            throw new Exception('No rule builder component found.');
        }

        return $builder;
    }

    protected function getNestedRuleBuilder(ComponentContainer $ruleBuilderBlockContainer, string $orGroupIndex): RuleBuilder
    {
        $builder = $ruleBuilderBlockContainer
            ->getComponent(fn (Component $component): bool => $component instanceof Repeater)
            ->getChildComponentContainer($orGroupIndex)
            ->getComponent(fn (Component $component): bool => $component instanceof RuleBuilder);

        if (! ($builder instanceof RuleBuilder)) {
            throw new Exception('No nested rule builder component found.');
        }

        return $builder;
    }

    /**
     * @param  array<string, mixed>  $rule
     */
    protected function tapOperatorFromRule(array $rule, ComponentContainer $ruleBuilderBlockContainer, Closure $callback): void
    {
        $constraint = $this->getConstraint($rule['type']);

        if (! $constraint) {
            return;
        }

        $operator = $rule['data'][$constraint::OPERATOR_SELECT_NAME];

        if (blank($operator)) {
            return;
        }

        [$operatorName, $isInverseOperator] = $constraint->parseOperatorString($operator);

        $operator = $constraint->getOperator($operatorName);

        if (! $operator) {
            return;
        }

        try {
            $ruleBuilderBlockContainer->validate();
        } catch (ValidationException) {
            return;
        }

        $constraint
            ->settings($rule['data']['settings'])
            ->inverse($isInverseOperator);

        $operator
            ->constraint($constraint)
            ->settings($rule['data']['settings'])
            ->inverse($isInverseOperator);

        $callback($operator);

        $constraint
            ->settings(null)
            ->inverse(null);

        $operator
            ->constraint(null)
            ->settings(null)
            ->inverse(null);
    }
}
