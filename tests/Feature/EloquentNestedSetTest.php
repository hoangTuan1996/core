<?php

namespace MediciVN\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MediciVN\Core\Tests\Models\Category;
use MediciVN\Core\Tests\Models\CategorySoftDelete;
use MediciVN\Core\Tests\TestCase;

class EloquentNestedSetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_must_has_a_root_node()
    {
        $this->assertDatabaseHas('categories', [
            'id' => Category::ROOT_ID,
            'name' => 'root',
            'slug' => 'root',
            'parent_id' => 0,
            'lft' => 1,
            'rgt' => 2,
            'depth' => 0,
        ]);

        $root = Category::find(Category::ROOT_ID);
        $this->assertNull($root);

        $root = Category::withoutGlobalScope('ignore_root')->find(Category::ROOT_ID);
        $this->assertEquals(Category::rootId(), $root->id);
    }

    /** @test */
    public function it_can_return_flat_tree()
    {
        Category::factory()->createMany([
            ["name" => "Category 2"],
            ["name" => "Category 3"],
            ["name" => "Category 4"],
            ["name" => "Category 5"],
            ["name" => "Category 6"],
            ["name" => "Category 7", "parent_id" => 3],
            ["name" => "Category 8", "parent_id" => 3],
            ["name" => "Category 9", "parent_id" => 3],
            ["name" => "Category 10", "parent_id" => 3],
            ["name" => "Category 11", "parent_id" => 5],
            ["name" => "Category 12", "parent_id" => 5],
            ["name" => "Category 13", "parent_id" => 6],
            ["name" => "Category 14", "parent_id" => 2],
            ["name" => "Category 15", "parent_id" => 2],
            ["name" => "Category 16", "parent_id" => 10],
            ["name" => "Category 17", "parent_id" => 10],
            ["name" => "Category 18", "parent_id" => 10]
        ]);

        $flatTree = Category::getFlatTree()->toArray();
        $c2 = Category::where('name', 'Category 2')->first();
        $c3 = Category::where('name', 'Category 3')->first();
        $c4 = Category::where('name', 'Category 4')->first();
        $c5 = Category::where('name', 'Category 5')->first();
        $c6 = Category::where('name', 'Category 6')->first();
        $c7 = Category::where('name', 'Category 7')->first();
        $c8 = Category::where('name', 'Category 8')->first();
        $c9 = Category::where('name', 'Category 9')->first();
        $c10 = Category::where('name', 'Category 10')->first();
        $c11 = Category::where('name', 'Category 11')->first();
        $c12 = Category::where('name', 'Category 12')->first();
        $c13 = Category::where('name', 'Category 13')->first();
        $c14 = Category::where('name', 'Category 14')->first();
        $c15 = Category::where('name', 'Category 15')->first();
        $c16 = Category::where('name', 'Category 16')->first();
        $c17 = Category::where('name', 'Category 17')->first();
        $c18 = Category::where('name', 'Category 18')->first();

        $this->assertEquals($c2->toArray(), $flatTree[0]);
        $this->assertEquals($c14->toArray(), $flatTree[1]);
        $this->assertEquals($c15->toArray(), $flatTree[2]);
        $this->assertEquals($c2->id, $c14->parent_id);
        $this->assertEquals($c2->id, $c15->parent_id);
        //
        $this->assertEquals($c3->toArray(), $flatTree[3]);
        $this->assertEquals($c7->toArray(), $flatTree[4]);
        $this->assertEquals($c8->toArray(), $flatTree[5]);
        $this->assertEquals($c9->toArray(), $flatTree[6]);
        $this->assertEquals($c10->toArray(), $flatTree[7]);
        $this->assertEquals($c16->toArray(), $flatTree[8]);
        $this->assertEquals($c17->toArray(), $flatTree[9]);
        $this->assertEquals($c18->toArray(), $flatTree[10]);
        $this->assertEquals($c3->id, $c7->parent_id);
        $this->assertEquals($c3->id, $c8->parent_id);
        $this->assertEquals($c3->id, $c9->parent_id);
        $this->assertEquals($c3->id, $c10->parent_id);
        $this->assertEquals($c10->id, $c16->parent_id);
        $this->assertEquals($c10->id, $c17->parent_id);
        $this->assertEquals($c10->id, $c18->parent_id);
        //
        $this->assertEquals($c4->toArray(), $flatTree[11]);
        //
        $this->assertEquals($c5->toArray(), $flatTree[12]);
        $this->assertEquals($c11->toArray(), $flatTree[13]);
        $this->assertEquals($c12->toArray(), $flatTree[14]);
        $this->assertEquals($c5->id, $c11->parent_id);
        $this->assertEquals($c5->id, $c12->parent_id);
        //
        $this->assertEquals($c6->toArray(), $flatTree[15]);
        $this->assertEquals($c13->toArray(), $flatTree[16]);
        $this->assertEquals($c6->id, $c13->parent_id);
    }

    /** @test */
    public function it_can_return_nested_tree()
    {
        Category::factory()->createMany([
            ["name" => "Category 2"],
            ["name" => "Category 3"],
            ["name" => "Category 4"],
            ["name" => "Category 5"],
            ["name" => "Category 6"],
            ["name" => "Category 7", "parent_id" => 3],
            ["name" => "Category 8", "parent_id" => 3],
            ["name" => "Category 9", "parent_id" => 3],
            ["name" => "Category 10", "parent_id" => 3],
            ["name" => "Category 11", "parent_id" => 5],
            ["name" => "Category 12", "parent_id" => 5],
            ["name" => "Category 13", "parent_id" => 6],
            ["name" => "Category 14", "parent_id" => 2],
            ["name" => "Category 15", "parent_id" => 2],
            ["name" => "Category 16", "parent_id" => 10],
            ["name" => "Category 17", "parent_id" => 10],
            ["name" => "Category 18", "parent_id" => 10]
        ]);

        $tree = Category::getTree();
        $c2 = Category::where('name', 'Category 2')->first();
        $c3 = Category::where('name', 'Category 3')->first();
        $c4 = Category::where('name', 'Category 4')->first();
        $c5 = Category::where('name', 'Category 5')->first();
        $c6 = Category::where('name', 'Category 6')->first();
        $c7 = Category::where('name', 'Category 7')->first();
        $c8 = Category::where('name', 'Category 8')->first();
        $c9 = Category::where('name', 'Category 9')->first();
        $c10 = Category::where('name', 'Category 10')->first();
        $c11 = Category::where('name', 'Category 11')->first();
        $c12 = Category::where('name', 'Category 12')->first();
        $c13 = Category::where('name', 'Category 13')->first();
        $c14 = Category::where('name', 'Category 14')->first();
        $c15 = Category::where('name', 'Category 15')->first();
        $c16 = Category::where('name', 'Category 16')->first();
        $c17 = Category::where('name', 'Category 17')->first();
        $c18 = Category::where('name', 'Category 18')->first();

        $this->assertEquals($c2->id, $tree[0]->id);
        $this->assertEquals($c3->id, $tree[1]->id);
        $this->assertEquals($c4->id, $tree[2]->id);
        $this->assertEquals($c5->id, $tree[3]->id);
        $this->assertEquals($c6->id, $tree[4]->id);

        $this->assertEquals($c2->children[0]->toArray(), $c14->toArray());
        $this->assertEquals($c2->children[1]->toArray(), $c15->toArray());
        //
        $this->assertEquals($c3->children[0]->toArray(), $c7->toArray());
        $this->assertEquals($c3->children[1]->toArray(), $c8->toArray());
        $this->assertEquals($c3->children[2]->toArray(), $c9->toArray());
        $this->assertEquals($c3->children[3]->toArray(), $c10->toArray());
        //
        $this->assertEquals($c10->children[0]->toArray(), $c16->toArray());
        $this->assertEquals($c10->children[1]->toArray(), $c17->toArray());
        $this->assertEquals($c10->children[2]->toArray(), $c18->toArray());
        //
        $this->assertEquals($c5->children[0]->toArray(), $c11->toArray());
        $this->assertEquals($c5->children[1]->toArray(), $c12->toArray());
        //
        $this->assertEquals($c6->children[0]->toArray(), $c13->toArray());
    }

    /** @test */
    public function it_can_calculate_rightly_lft_rgt_depth_for_new_nodes()
    {
        $root = Category::withoutGlobalScope('ignore_root')->find(Category::ROOT_ID);
        $this->assertEquals([1, 2], [$root->lft, $root->rgt]);

        Category::factory()->createMany([
            ["name" => "Category 2"],
            ["name" => "Category 3"],
            ["name" => "Category 4"],
            ["name" => "Category 5"],
            ["name" => "Category 6"],
        ]);

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 3], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 4, 5], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 6, 7], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 8, 9], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 10, 11], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([Category::ROOT_ID, 12], [$root->lft, $root->rgt]);

        // Check when adding new child nodes to Category 3
        Category::factory()->createMany([
            ["name" => "Category 7", "parent_id" => 3],
            ["name" => "Category 8", "parent_id" => 3],
            ["name" => "Category 9", "parent_id" => 3],
            ["name" => "Category 10", "parent_id" => 3],
        ]);

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 3, ], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 4, 13], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 5, 6], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 7, 8], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 9, 10], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 10', 11, 12], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 14, 15], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 16, 17], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 18, 19], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([1, 20], [$root->lft, $root->rgt]);

        // Check when adding new child nodes to Category 5
        Category::factory()->createMany([
            ["name" => "Category 11", "parent_id" => 5],
            ["name" => "Category 12", "parent_id" => 5],
        ]);

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 3], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 4, 13], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 5, 6], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 7, 8], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 9, 10], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 10', 11, 12], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 14, 15], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 16, 21], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 17, 18], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 19, 20], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 22, 23], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([1, 24], [$root->lft, $root->rgt]);

        // Check when adding new child nodes to Category 6
        Category::factory()->createMany([
            ["name" => "Category 13", "parent_id" => 6]
        ]);

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 3], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 4, 13], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 5, 6], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 7, 8], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 9, 10], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 10', 11, 12], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 14, 15], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 16, 21], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 17, 18], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 19, 20], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 22, 25], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 23, 24], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 26], [$root->lft, $root->rgt]);

        // Check when adding new child nodes to Category 2
        Category::factory()->createMany([
            ["name" => "Category 14", "parent_id" => 2],
            ["name" => "Category 15", "parent_id" => 2],
        ]);

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 7], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 14', 3, 4], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 15', 5, 6], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 8, 17], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 9, 10], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 11, 12], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 13, 14], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 10', 15, 16], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 18, 19], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 20, 25], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 21, 22], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 23, 24], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 26, 29], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 27, 28], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 30], [$root->lft, $root->rgt]);

        // Check when adding new child nodes to Category 10
        Category::factory()->createMany([
            ["name" => "Category 16", "parent_id" => 10],
            ["name" => "Category 17", "parent_id" => 10],
            ["name" => "Category 18", "parent_id" => 10]
        ]);

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 7], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 14', 3, 4], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 15', 5, 6], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 8, 23], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 9, 10], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 11, 12], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 13, 14], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 10', 15, 22], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 16', 16, 17], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 17', 18, 19], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 18', 20, 21], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 24, 25], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 26, 31], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 27, 28], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 29, 30], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 32, 35], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 33, 34], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);
    }

    /** @test */
    public function it_can_calculate_rightly_lft_rgt_when_update()
    {
        $root = Category::withoutGlobalScope('ignore_root')->find(Category::ROOT_ID);
        $this->assertEquals([1, 2], [$root->lft, $root->rgt]);

        Category::factory()->createMany([
            ["name" => "Category 2"],
            ["name" => "Category 3"],
            ["name" => "Category 4"],
            ["name" => "Category 5"],
            ["name" => "Category 6"],
            ["name" => "Category 7", "parent_id" => 3],
            ["name" => "Category 8", "parent_id" => 3],
            ["name" => "Category 9", "parent_id" => 3],
            ["name" => "Category 10", "parent_id" => 3],
            ["name" => "Category 11", "parent_id" => 5],
            ["name" => "Category 12", "parent_id" => 5],
            ["name" => "Category 13", "parent_id" => 6],
            ["name" => "Category 14", "parent_id" => 2],
            ["name" => "Category 15", "parent_id" => 2],
            ["name" => "Category 16", "parent_id" => 10],
            ["name" => "Category 17", "parent_id" => 10],
            ["name" => "Category 18", "parent_id" => 10]
        ]);

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 7], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 14', 3, 4], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 15', 5, 6], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 8, 23], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 9, 10], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 11, 12], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 13, 14], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 10', 15, 22], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 16', 16, 17], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 17', 18, 19], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 18', 20, 21], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 24, 25], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 26, 31], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 27, 28], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 29, 30], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 32, 35], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 33, 34], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);

        // Move Category 10 from Category 3 into Category 2
        $c10->parent_id = $c2->id;
        $c10->save();

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 15], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 14', 3, 4], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 15', 5, 6], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 10', 7, 14], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 16', 8, 9], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 17', 10, 11], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 18', 12, 13], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 16, 23], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 17, 18], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 19, 20], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 21, 22], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 24, 25], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 26, 31], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 27, 28], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 29, 30], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 32, 35], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 33, 34], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);

        // Move Category 2 into Category 4
        $c2->parent_id = $c4->id;
        $c2->save();

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 2, 9], [$c3->parent_id, $c3->depth ,$c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 3, 4], [$c7->parent_id, $c7->depth ,$c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 5, 6], [$c8->parent_id, $c8->depth ,$c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 7, 8], [$c9->parent_id, $c9->depth ,$c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 10, 25], [$c4->parent_id, $c4->depth ,$c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([$c4->id, 2, 'Category 2', 11, 24], [$c2->parent_id, $c2->depth ,$c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 3, 'Category 14', 12, 13], [$c14->parent_id, $c14->depth ,$c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 3, 'Category 15', 14, 15], [$c15->parent_id, $c15->depth ,$c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([$c2->id, 3, 'Category 10', 16, 23], [$c10->parent_id, $c10->depth ,$c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 4, 'Category 16', 17, 18], [$c16->parent_id, $c16->depth ,$c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 4, 'Category 17', 19, 20], [$c17->parent_id, $c17->depth ,$c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 4, 'Category 18', 21, 22], [$c18->parent_id, $c18->depth ,$c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 26, 31], [$c5->parent_id, $c5->depth ,$c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 27, 28], [$c11->parent_id, $c11->depth ,$c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 29, 30], [$c12->parent_id, $c12->depth ,$c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 32, 35], [$c6->parent_id, $c6->depth ,$c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 33, 34], [$c13->parent_id, $c13->depth ,$c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);

        // Move Category 4 into Category 6
        $c4->parent_id = $c6->id;
        $c4->save();

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 2, 9], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 3, 4], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 5, 6], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 7, 8], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 10, 15], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 11, 12], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 13, 14], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 16, 35], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 17, 18], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 4', 19, 34], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([$c4->id, 3, 'Category 2', 20, 33], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 4, 'Category 14', 21, 22], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 4, 'Category 15', 23, 24], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([$c2->id, 4, 'Category 10', 25, 32], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 5, 'Category 16', 26, 27], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 5, 'Category 17', 28, 29], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 5, 'Category 18', 30, 31], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);

        // Move Category 6 into Category 5
        $c6->parent_id = $c5->id;
        $c6->save();

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 2, 9], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 3, 4], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 5, 6], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 7, 8], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 10, 35], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 11, 12], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 13, 14], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 6', 15, 34], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 3, 'Category 13', 16, 17], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([$c6->id, 3, 'Category 4', 18, 33], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([$c4->id, 4, 'Category 2', 19, 32], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 5, 'Category 14', 20, 21], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 5, 'Category 15', 22, 23], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([$c2->id, 5, 'Category 10', 24, 31], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 6, 'Category 16', 25, 26], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 6, 'Category 17', 27, 28], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 6, 'Category 18', 29, 30], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);

        // remove parent of Category 10, expect root node to be automatically assigned instead
        $c10->parent_id = null;
        $c10->save();

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 2, 9], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 3, 4], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 5, 6], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 7, 8], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 10, 27], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 11, 12], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 13, 14], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 6', 15, 26], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 3, 'Category 13', 16, 17], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([$c6->id, 3, 'Category 4', 18, 25], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([$c4->id, 4, 'Category 2', 19, 24], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 5, 'Category 14', 20, 21], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 5, 'Category 15', 22, 23], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 10', 28, 35], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 2, 'Category 16', 29, 30], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 2, 'Category 17', 31, 32], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 2, 'Category 18', 33, 34], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);
    }

    /** @test */
    public function it_can_calculate_rightly_lft_rgt_depth_when_delete()
    {
        $root = Category::withoutGlobalScope('ignore_root')->find(Category::ROOT_ID);
        $this->assertEquals([1, 2], [$root->lft, $root->rgt]);

        Category::factory()->createMany([
            ["name" => "Category 2"],
            ["name" => "Category 3"],
            ["name" => "Category 4"],
            ["name" => "Category 5"],
            ["name" => "Category 6"],
            ["name" => "Category 7", "parent_id" => 3],
            ["name" => "Category 8", "parent_id" => 3],
            ["name" => "Category 9", "parent_id" => 3],
            ["name" => "Category 10", "parent_id" => 3],
            ["name" => "Category 11", "parent_id" => 5],
            ["name" => "Category 12", "parent_id" => 5],
            ["name" => "Category 13", "parent_id" => 6],
            ["name" => "Category 14", "parent_id" => 2],
            ["name" => "Category 15", "parent_id" => 2],
            ["name" => "Category 16", "parent_id" => 10],
            ["name" => "Category 17", "parent_id" => 10],
            ["name" => "Category 18", "parent_id" => 10]
        ]);

        $root->refresh();
        $categories = Category::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 7], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 14', 3, 4], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 15', 5, 6], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 8, 23], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 9, 10], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 11, 12], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 13, 14], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 10', 15, 22], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 16', 16, 17], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 17', 18, 19], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 18', 20, 21], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 24, 25], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 26, 31], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 27, 28], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 29, 30], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 32, 35], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 33, 34], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);

        // Delete Category 10, expect Category 16, 17, 18 to be moved to Category 3
        $c10->delete();
        $root->refresh();
        $categories->each(function ($category) { $category->refresh(); });

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 2', 2, 7], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 14', 3, 4], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 15', 5, 6], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 3', 8, 21], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 9, 10], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 11, 12], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 13, 14], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 16', 15, 16], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 17', 17, 18], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 18', 19, 20], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 4', 22, 23], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 5', 24, 29], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 25, 26], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 27, 28], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 6', 30, 33], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 31, 32], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 34], [$root->lft, $root->rgt]);

        // Delete Category 2, 3, 4, 5, 6
        $c2->delete();
        $c3->delete();
        $c4->delete();
        $c5->delete();
        $c6->delete();
        $root->refresh();
        $categories->each(function ($category) { $category->refresh(); });

        $this->assertEquals([Category::ROOT_ID, 1, 'Category 14', 2, 3], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 15', 4, 5], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 7', 6, 7], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 8', 8, 9], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 9', 10, 11], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 16', 12, 13], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 17', 14, 15], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 18', 16, 17], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 11', 18, 19], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 12', 20, 21], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([Category::ROOT_ID, 1, 'Category 13', 22, 23], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 24], [$root->lft, $root->rgt]);
    }

    /** @test */
    public function it_can_calculate_rightly_lft_rgt_depth_when_soft_delete()
    {
        $root = CategorySoftDelete::withoutGlobalScope('ignore_root')->find(CategorySoftDelete::ROOT_ID);
        $this->assertEquals([1, 2], [$root->lft, $root->rgt]);

        CategorySoftDelete::factory()->createMany([
            ["name" => "Category 2"],
            ["name" => "Category 3"],
            ["name" => "Category 4"],
            ["name" => "Category 5"],
            ["name" => "Category 6"],
            ["name" => "Category 7", "parent_id" => 3],
            ["name" => "Category 8", "parent_id" => 3],
            ["name" => "Category 9", "parent_id" => 3],
            ["name" => "Category 10", "parent_id" => 3],
            ["name" => "Category 11", "parent_id" => 5],
            ["name" => "Category 12", "parent_id" => 5],
            ["name" => "Category 13", "parent_id" => 6],
            ["name" => "Category 14", "parent_id" => 2],
            ["name" => "Category 15", "parent_id" => 2],
            ["name" => "Category 16", "parent_id" => 10],
            ["name" => "Category 17", "parent_id" => 10],
            ["name" => "Category 18", "parent_id" => 10]
        ]);

        $root->refresh();
        $categories = CategorySoftDelete::all();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 2', 2, 7], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 14', 3, 4], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 15', 5, 6], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 3', 8, 23], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 9, 10], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 11, 12], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 13, 14], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 10', 15, 22], [$c10->parent_id, $c10->depth, $c10->name, $c10->lft, $c10->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 16', 16, 17], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 17', 18, 19], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c10->id, 3, 'Category 18', 20, 21], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 4', 24, 25], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 5', 26, 31], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 27, 28], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 29, 30], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 6', 32, 35], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 33, 34], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 36], [$root->lft, $root->rgt]);

        // Delete Category 10, expect Category 16, 17, 18 to be moved to Category 3
        $c10->delete();
        $root->refresh();
        $categories = CategorySoftDelete::withTrashed()->get();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertSoftDeleted($c10);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 2', 2, 7], [$c2->parent_id, $c2->depth, $c2->name, $c2->lft, $c2->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 14', 3, 4], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([$c2->id, 2, 'Category 15', 5, 6], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 3', 8, 21], [$c3->parent_id, $c3->depth, $c3->name, $c3->lft, $c3->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 7', 9, 10], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 8', 11, 12], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 9', 13, 14], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 16', 15, 16], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 17', 17, 18], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([$c3->id, 2, 'Category 18', 19, 20], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 4', 22, 23], [$c4->parent_id, $c4->depth, $c4->name, $c4->lft, $c4->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 5', 24, 29], [$c5->parent_id, $c5->depth, $c5->name, $c5->lft, $c5->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 11', 25, 26], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([$c5->id, 2, 'Category 12', 27, 28], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 6', 30, 33], [$c6->parent_id, $c6->depth, $c6->name, $c6->lft, $c6->rgt]);
        $this->assertEquals([$c6->id, 2, 'Category 13', 31, 32], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 34], [$root->lft, $root->rgt]);

        // Delete Category 2, 3, 4, 5, 6
        $c2->delete();
        $c3->delete();
        $c4->delete();
        $c5->delete();
        $c6->delete();

        $root->refresh();
        $categories = CategorySoftDelete::withTrashed()->get();
        [$c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $c16, $c17, $c18] = $categories;

        $this->assertSoftDeleted($c10);
        $this->assertSoftDeleted($c2);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 14', 2, 3], [$c14->parent_id, $c14->depth, $c14->name, $c14->lft, $c14->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 15', 4, 5], [$c15->parent_id, $c15->depth, $c15->name, $c15->lft, $c15->rgt]);

        $this->assertSoftDeleted($c3);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 7', 6, 7], [$c7->parent_id, $c7->depth, $c7->name, $c7->lft, $c7->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 8', 8, 9], [$c8->parent_id, $c8->depth, $c8->name, $c8->lft, $c8->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 9', 10, 11], [$c9->parent_id, $c9->depth, $c9->name, $c9->lft, $c9->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 16', 12, 13], [$c16->parent_id, $c16->depth, $c16->name, $c16->lft, $c16->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 17', 14, 15], [$c17->parent_id, $c17->depth, $c17->name, $c17->lft, $c17->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 18', 16, 17], [$c18->parent_id, $c18->depth, $c18->name, $c18->lft, $c18->rgt]);

        $this->assertSoftDeleted($c4);
        $this->assertSoftDeleted($c5);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 11', 18, 19], [$c11->parent_id, $c11->depth, $c11->name, $c11->lft, $c11->rgt]);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 12', 20, 21], [$c12->parent_id, $c12->depth, $c12->name, $c12->lft, $c12->rgt]);

        $this->assertSoftDeleted($c6);
        $this->assertEquals([CategorySoftDelete::ROOT_ID, 1, 'Category 13', 22, 23], [$c13->parent_id, $c13->depth, $c13->name, $c13->lft, $c13->rgt]);
        $this->assertEquals([1, 24], [$root->lft, $root->rgt]);
    }
}