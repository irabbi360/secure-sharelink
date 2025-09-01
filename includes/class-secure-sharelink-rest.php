<?php
class ShareLink_REST {
    public function __construct() {
        add_action('rest_api_init', function() {
            register_rest_route('sharelink/v1', '/create', [
                'methods' => 'POST',
                'callback' => [$this, 'create_link'],
                'permission_callback' => function() { return current_user_can('manage_options'); }
            ]);
        });
    }

    public function create_link($request) {
        $params = $request->get_json_params();
        $manager = new ShareLink_Manager();
        return [
            'url' => $manager->create_link($params['type'], $params['value'], $params)
        ];
    }
}
