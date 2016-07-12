<?php

/**
 * @file
 * Contains \Drupal\like_dislike\Controller\LikeDislikeController.
 */

namespace Drupal\like_dislike\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class LikeDislikeController.
 *
 * @package Drupal\like_dislike\Controller
 */
class LikeDislikeController extends ControllerBase {

  /**
   * Like or Dislike handler.
   */
  public function handler($clicked, $data) {
    $decode_data = json_decode(base64_decode($data));

    $entity_data = \Drupal::entityManager()
      ->getStorage($decode_data->entity_type)
      ->load($decode_data->entity_id);
    $field_name = $decode_data->field_name;

    if ($clicked == 'like') {
      $entity_data->$field_name->likes++;
    }
    elseif ($clicked == 'dislike') {
      $entity_data->$field_name->dislikes--;
    }

    $existing_users = json_decode($entity_data->$field_name->clicked_by);
    $existing_users = ($existing_users != NULL) ? $existing_users : new \stdClass();
    $user = (int) $decode_data->uid;
    $ajax_response = new AjaxResponse();
    if (!array_key_exists($user, (array) $existing_users)) {
      $existing_users->$user = $user;
      $entity_data->$field_name->clicked_by = json_encode($existing_users);
      $entity_data->save();

      return $ajax_response->addCommand(new HtmlCommand('#like', 'karthik like/dislike..!'));
    }
    else {
      return $ajax_response->addCommand(new HtmlCommand('#like', 'karthik already liked/disliked :('));
    }
  }

}
