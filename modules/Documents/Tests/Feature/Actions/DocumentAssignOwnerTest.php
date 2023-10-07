<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */

namespace Modules\Documents\Tests\Feature\Actions;

use Modules\Core\Tests\ResourceTestCase;
use Modules\Users\Actions\AssignOwnerAction;

class DocumentAssignOwnerTest extends ResourceTestCase
{
    protected $action;

    protected $resourceName = 'documents';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AssignOwnerAction;
    }

    protected function tearDown(): void
    {
        unset($this->action);
        parent::tearDown();
    }

    public function test_super_admin_user_can_run_document_assign_owner_action()
    {
        $this->signIn();
        $user = $this->createUser();
        $document = $this->factory()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'user_id' => $user->id,
            'ids' => [$document->id],
        ])->assertOk();

        $this->assertEquals($user->id, $document->fresh()->user_id);
    }

    public function test_authorized_user_can_run_document_assign_owner_action()
    {
        $this->asRegularUser()->withPermissionsTo('edit all documents')->signIn();

        $user = $this->createUser();
        $document = $this->factory()->for($user)->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'user_id' => $user->id,
            'ids' => [$document->id],
        ])->assertOk();

        $this->assertEquals($user->id, $document->fresh()->user_id);
    }

    public function test_unauthorized_user_can_run_document_assign_owner_action_on_own_document()
    {
        $signedInUser = $this->asRegularUser()->withPermissionsTo('edit own documents')->signIn();
        $user = $this->createUser();

        $documentForSignedIn = $this->factory()->for($signedInUser)->create();
        $otherDocument = $this->factory()->create();

        $this->postJson($this->actionEndpoint($this->action), [
            'user_id' => $user->id,
            'ids' => [$otherDocument->id],
        ])->assertJson(['error' => __('users::user.not_authorized')]);

        $this->postJson($this->actionEndpoint($this->action), [
            'user_id' => $user->id,
            'ids' => [$documentForSignedIn->id],
        ]);

        $this->assertEquals($user->id, $documentForSignedIn->fresh()->user_id);
    }

    public function test_document_assign_owner_action_requires_owner()
    {
        $this->signIn();

        $this->postJson($this->actionEndpoint($this->action), [
            'ids' => [],
        ])->assertJsonValidationErrors(['user_id']);
    }
}
