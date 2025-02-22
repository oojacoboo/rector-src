<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\ReplaceParentCallByPropertyCallRectorTest
 */
final class ReplaceParentCallByPropertyCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    final public const PARENT_CALLS_TO_PROPERTIES = 'parent_calls_to_properties';

    /**
     * @var ReplaceParentCallByPropertyCall[]
     */
    private array $parentCallToProperties = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes method calls in child of specific types to defined property method call',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(SomeTypeToReplace $someTypeToReplace)
    {
        $someTypeToReplace->someMethodCall();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(SomeTypeToReplace $someTypeToReplace)
    {
        $this->someProperty->someMethodCall();
    }
}
CODE_SAMPLE
                    ,
                    [new ReplaceParentCallByPropertyCall('SomeTypeToReplace', 'someMethodCall', 'someProperty')]
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->parentCallToProperties as $parentCallToProperty) {
            if (! $this->isObjectType($node->var, $parentCallToProperty->getObjectType())) {
                continue;
            }

            if (! $this->isName($node->name, $parentCallToProperty->getMethod())) {
                continue;
            }

            $node->var = $this->nodeFactory->createPropertyFetch('this', $parentCallToProperty->getProperty());
            return $node;
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $parentCallToProperties = $configuration[self::PARENT_CALLS_TO_PROPERTIES] ?? $configuration;
        Assert::allIsAOf($parentCallToProperties, ReplaceParentCallByPropertyCall::class);

        $this->parentCallToProperties = $parentCallToProperties;
    }
}
