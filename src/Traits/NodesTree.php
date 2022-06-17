<?php

namespace MediciVN\Core\Traits;

trait NodesTree
{
    /**
     * @return mixed
     */
    public function updateNestedNodes()
    {
        $nodes = $this->getAllNodes();
        $nestedSetInfo = $this->_getNewNestedSetInfo($nodes);
        foreach ($nestedSetInfo as $nodeId => $node) {
            $node->save();
        }

        return $nodes;
    }

    /**
     * @param bool $nestedOrdering
     * @return mixed
     */
    protected function getAllNodes($nestedOrdering = false)
    {
        if ($nestedOrdering) {
            return self::orderBy('lft', 'ASC')->get();
        } else {
            return self::orderBy('order', 'ASC')->get();
        }
    }

    /**
     * @param null $nodes
     * @return array
     */
    protected function getNodeHierarchy($nodes = null)
    {
        if (empty($nodes) || $nodes === null) {
            $nodes = $this->getAllNodes();
        }

        $nodeHierarchy = array();

        foreach ($nodes as $node) {
            $nodeHierarchy[$node->parent_id][$node->agency_id] = $node;
        }

        return $nodeHierarchy;
    }

    /**
     * Builds lft, rgt and depth values for all nodes, based on the parent_node_id and display_order information in the database.
     * Also rebuilds the effective style ID.
     *
     * @param array|null $nodeHierarchy - will be fetched automatically when NULL is provided
     * @param integer $parentNodeId
     * @param integer $depth
     * @param integer $lft The entry left value; note that this will be changed and returned as the rgt value
     *
     * @return array [id] => array(lft => int, rgt => int)...
     */
    protected function _getNewNestedSetInfo($nodeHierarchy = null, $parentNodeId = 0, $depth = 0, &$lft = 1)
    {
        $nodes = array();

        if ($depth == 0) {
            $nodeHierarchy = $this->getNodeHierarchy($nodeHierarchy);
        }

        if (empty($nodeHierarchy[$parentNodeId])) {
            return array();
        }

        foreach ($nodeHierarchy[$parentNodeId] as $i => $node) {
            $node->lft = $lft++;
            $node->depth = $depth;

            $nodes += $this->_getNewNestedSetInfo($nodeHierarchy, $node->agency_id, $depth + 1, $lft);

            $node->rgt = $lft++;
            $nodes[$node->id] = $node;
        }

        return $nodes;
    }
}
