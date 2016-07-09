<?php

/**
 * @file
 * Contains \Drupal\like_dislike\Controller\LikeDislikeController.
 */

namespace Drupal\like_dislike\Controller;

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
    $decode_data = json_decode($data);
    if ($clicked == 'like') {
      $entity_data = \Drupal::entityManager()
        ->getStorage($decode_data->entity_type)
        ->load($decode_data->entity_id);
      $values = [
        'und' => [
          0 => [
            'like' => 12,
            'dislike' => 1
          ],
        ],
      ];
      //$entity_data->set($decode_data->field_name, $values);
      //$entity_data->set($decode_data->field_name['dislike'], 123);
      //$entity_data->save();
    }
    elseif ($clicked == 'dislike') {

    }
    return [
      '#type' => 'markup',
      '#markup' => $data
    ];
  }

}
