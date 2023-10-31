<?php

define('DB_PATH', __DIR__ . '/db.json');
define('SETTINGS_PATH', __DIR__ . '/settings.json');

function inject_to_db($data) {
    file_put_contents(DB_PATH, json_encode($data));
}

function update_settings($settings) {
    file_put_contents(SETTINGS_PATH, json_encode($settings));
}

function response($data) {
    return json_encode($data);
}

$request = json_decode(file_get_contents('php://input'), true);
$data = json_decode(file_get_contents(DB_PATH), true);
$settings = json_decode(file_get_contents(SETTINGS_PATH), true);

$getAll = function () use ($data) {
    return response($data);
};

$getOne = function ($id) use ($data) {
    foreach ($data as $std) {
        if ($std['id'] == $id)
            return response($std);
    }
    return response([]);
};

$deleteOne = function ($id) use ($data) {
    foreach ($data as $i => $std) {
        if ($std['id'] == $id) {
            unset($data[$i]);
            $data = array_values($data);
            inject_to_db($data);
            return response($data);
        }
    }
    return response([]);
};

$post = function () use ($data, $settings, $request) {
    $insert = [];
    $insert['id'] = $settings['counter']++;
    $insert['name'] = $request['name'];
    $insert['age'] = $request['age'];
    $insert['email'] = $request['email'];
    $data[] = $insert;
    update_settings($settings);
    inject_to_db($data);
    return response($insert);
};

$updateOne = function ($id) use ($data, $request) {
    foreach ($data as &$std) {
        if ($std['id'] == $id) {
            $std['name'] = isset($request['name']) ? $request['name'] : $std['name'];
            $std['email'] = isset($request['email']) ? $request['email'] : $std['email'];
            $std['age'] = isset($request['age']) ? $request['age'] : $std['age'];
            inject_to_db($data);
            return response($std);
        }
    }
    return response([]);
};

$uri = trim($_SERVER['REQUEST_URI'], '/');
$parts = explode('/', $uri);
$last = array_pop($parts);
$method = strtolower($_SERVER['REQUEST_METHOD']);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header('Content-Type: application/json');

switch ($last) {
    case 'students':
        if ($method == 'get')
            echo $getAll();
        elseif ($method == 'post')
            echo $post();
        break;
    default:
        if ($method == 'get')
            echo $getOne($last);
        elseif ($method == 'delete')
            echo $deleteOne($last);
        elseif ($method == 'put' || $method == 'patch')
            echo $updateOne($last);
        break;
}
