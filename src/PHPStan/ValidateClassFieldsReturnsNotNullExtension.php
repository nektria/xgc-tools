<?php

declare(strict_types=1);

namespace Xgc\PHPStan;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\StaticMethodTypeSpecifyingExtension;
use PHPStan\Type\TypeCombinator;
use Xgc\Utils\Validate;

class ValidateClassFieldsReturnsNotNullExtension implements
    TypeSpecifierAwareExtension,
    StaticMethodTypeSpecifyingExtension
{
    private TypeSpecifier $typeSpecifier;

    public function getClass(): string
    {
        return Validate::class;
    }

    public function isStaticMethodSupported(
        MethodReflection $staticMethodReflection,
        StaticCall $node,
        TypeSpecifierContext $context
    ): bool {
        return $staticMethodReflection->getName() === 'classFieldsReturnsNotNull' && $context->null();
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }

    public function specifyTypes(
        MethodReflection $staticMethodReflection,
        StaticCall $node,
        Scope $scope,
        TypeSpecifierContext $context
    ): SpecifiedTypes {
        $fields = $node->getArgs()[2]->value;
        $objectExpr = $node->getArgs()[0]->value;
        $objectType = $scope->getType($objectExpr);

        if (!$fields instanceof Array_) {
            return new SpecifiedTypes();
        }

        $specifiedTypes = new SpecifiedTypes();
        foreach ($fields->items as $fieldItem) {
            if ($fieldItem->value instanceof String_) {
                $fieldName = $fieldItem->value->value;
                $propertyType = $objectType->getProperty($fieldName, $scope)->getReadableType();
                $newType = TypeCombinator::removeNull($propertyType);
                $propertyExpr = new PropertyFetch($objectExpr, $fieldName);
                $specifiedTypes = $specifiedTypes->unionWith(
                    $this->typeSpecifier->create(
                        $propertyExpr,
                        $newType,
                        TypeSpecifierContext::createTruthy(),
                        scope: $scope
                    ),
                );
            }
        }

        return $specifiedTypes;
    }
}
