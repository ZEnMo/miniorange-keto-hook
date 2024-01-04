<?php

namespace Zenmo;

use Ory\Client\Api\PermissionApi;
use Ory\Client\Api\RelationshipApi;
use Ory\Client\Configuration;
use Ory\Client\Model\ErrorGeneric;

class ZenmoRoleSync
{
    private string $oryApiKey;

    public static function setup()
    {
        $self = new self(require(__DIR__ . "/../ory-api-key.php"));
        add_action('mo_oauth_get_user_attrs', [$self,'addUserToGroups'], 10, 2);
    }

    public function __construct(string $oryApiKey)
    {
        $this->oryApiKey = $oryApiKey;
    }

    /**
     * Get user roles from Ory Keto
     *
     * Never fail, just continue and log error
     */
    public function addUserToGroups(\WP_User $wordPressUser, array $idToken): void
    {
        $oryBaseUrl = $idToken["iss"];
        $oryUserUuid = $idToken["sub"];

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($this->oryApiKey)
            ->setHost($oryBaseUrl);

        $permissionApi = new PermissionApi(null, $config);
        $relationshipApi = new RelationshipApi(null, $config);

        $checkPermissionResponse = $permissionApi->checkPermission('WordPress', 'wordpress', 'admins', "User:$oryUserUuid");
        if ($checkPermissionResponse instanceof ErrorGeneric) {
            error_Log($checkPermissionResponse);
            return;
        }

        $isAdmin = $checkPermissionResponse->getAllowed();
        if ($isAdmin) {
            $wordPressUser->add_role('administrator');
        } else {
            $wordPressUser->remove_role('administrator');
        }

        $getRelationshipsResponse = $relationshipApi->getRelationships(null, null, "Project", null, "members", "User:$oryUserUuid");
        if ($getRelationshipsResponse instanceof ErrorGeneric) {
            error_log($getRelationshipsResponse);
            return;
        }

        $relationTuples = $getRelationshipsResponse->getRelationTuples() ?? [];
        foreach ($relationTuples as $relationTuple) {
            // e.g. hessenpoort, drechtsteden, de_wieken
            $project = $relationTuple->getObject();
            $wordPressUser->add_role($project);
        }

        wp_update_user($wordPressUser);
    }
}
