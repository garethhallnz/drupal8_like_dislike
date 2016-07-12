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
   *
   * @param string $clicked
   *   Status of the click link.
   * @param string $data
   *   Data passed from the formatter.
   *
   * @return AjaxResponse|string
   *   Response count for the like/dislike.
   */
  public function handler($clicked, $data) {
    $return = '';
    $response = new AjaxResponse();

    $decode_data = json_decode(base64_decode($data));

    $entity_data = \Drupal::entityTypeManager()
      ->getStorage($decode_data->entity_type)
      ->load($decode_data->entity_id);
    $field_name = $decode_data->field_name;

    $users = json_decode($entity_data->$field_name->clicked_by);
    if ($users == NULL) {
      $users = new \stdClass();
      $users->default = 'default';
    }
    $user = \Drupal::currentUser()->id();

    $already_clicked = key_exists($user, array_keys((array) $users));
    if ($clicked == 'like') {
      if (!$already_clicked) {
        $entity_data->$field_name->likes++;
        $users->$user = 'like';
      }
      $return = $response->addCommand(new HtmlCommand('#like', $entity_data->$field_name->likes));
    }
    elseif ($clicked == 'dislike') {
      if (!$already_clicked) {
        $entity_data->$field_name->dislikes--;
        $users->$user = "dislike";
      }
      $return = $response->addCommand(new HtmlCommand('#dislike', $entity_data->$field_name->dislikes));
    }
    $entity_data->$field_name->clicked_by = json_encode($users);
    $entity_data->save();
    return $return;
  }

}
