<?php
  
  function main($data): array {
    
    $clickupApiToken = getenv('CLICKUP_API_TOKEN');
    $listId = getenv('LIST_ID');

    define('CLICKUP_API_TOKEN', $clickupApiToken);
    define('LIST_ID', $listId);

    $taskName = $data['payload']['name'];
    $task = $data['payload']['id'];
    $quantity = intval($data['payload']['custom_fields'][7]['value']);

    // index 12 == Content Type (CT)
    $customfields["content_type"] = array(
        "id" => $data['payload']['custom_fields'][12]['id'],
        "value" => $data['payload']['custom_fields'][12]['value']
    );

    // index 14 == Product Category (CT)
    $customfields["product_category"] = array(
      "id" => $data['payload']['custom_fields'][14]['id'],
      "value" => $data['payload']['custom_fields'][14]['value']
    );

    for ($i = 0; $i <= $quantity; $i++) {
        $subtaskName = $taskName . ' - Subtask ' . $i + 1;
        createSubTask($task, $subtaskName, $customfields);
    }

    return [
        'body' => 'success',
    ];
    
}

function createSubTask($task, $subtaskName, $customfields) {
  $url = 'https://api.clickup.com/api/v2/list/' . LIST_ID . '/task';
  $headers = [
      'Authorization: ' . CLICKUP_API_TOKEN,
      'Content-Type: application/json'
  ];

  $payload = json_encode([
      'name' => $subtaskName,
      'status' => 'Open',
      'parent'=> $task,
      "custom_fields" => array(

        $customfields["content_type"],
        $customfields["product_category"]
      )
  ]);
  
  $response = makeApiRequest($url, $headers, $payload);

  if ($response && isset($response['id'])) {
      return $response['id'];
  }

  return null;
}

function makeApiRequest($url, $headers, $payload) {
  
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

  $response = curl_exec($ch);
  curl_close($ch);

  return json_decode($response, true);
}

