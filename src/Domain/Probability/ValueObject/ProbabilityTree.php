<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Domain\Probability\ValueObject;

use Cliffordvickrey\TheGambler\Domain\Contract\PortableInterface;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Draw;
use OutOfBoundsException;
use UnexpectedValueException;
use function count;
use function is_array;
use function serialize;
use function sprintf;
use function unserialize;

class ProbabilityTree implements PortableInterface
{
    private $nodes;

    public function __construct(ProbabilityNode...$nodes)
    {
        $this->nodes = $nodes;
    }

    public function getNode(Draw $draw): ProbabilityNode
    {
        $drawId = $draw->getId();
        foreach ($this->nodes as $node) {
            if ($drawId === $node->getDraw()->getId()) {
                return $node;
            }
        }

        throw new OutOfBoundsException(sprintf('No node with draw ID %d found in tree', $drawId));
    }

    public function unserialize($serialized)
    {
        $unSerialized = unserialize($serialized, ['allowed_classes' => [Draw::class, ProbabilityNode::class]]);
        if (!is_array($unSerialized)) {
            throw new UnexpectedValueException('Expected array');
        }
        $tree = new static(...$unSerialized);
        $this->nodes = $tree->nodes;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function serialize()
    {
        return serialize($this->nodes);
    }

    public function jsonSerialize(): array
    {
        $highNode = $this->getNodeWithHighestMeanPayout();
        $highDraw = $highNode->getDraw();
        return ['nodes' => $this->nodes, 'highDraw' => $highDraw];
    }

    public function getNodeWithHighestMeanPayout(): ProbabilityNode
    {
        if (0 === count($this->nodes)) {
            throw new OutOfBoundsException('No nodes in probability tree');
        }

        $max = -1.0;
        $maxIndex = 0;

        foreach ($this->nodes as $i => $node) {
            $meanPayout = $node->getMeanPayout();
            if ($max < $meanPayout) {
                $max = $meanPayout;
                $maxIndex = $i;
            }
        }

        return $this->nodes[$maxIndex];
    }
}
