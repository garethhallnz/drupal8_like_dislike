<?php

/**
 * @file
 * Contains \Drupal\like_dislike\Plugin\Field\FieldFormatter\LikeDislikeFormatter.
 */

namespace Drupal\like_dislike\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'like_dislike_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "like_dislike_formatter",
 *   label = @Translation("Like Dislike"),
 *   field_types = {
 *     "like_dislike"
 *   }
 * )
 */
class LikeDislikeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      // Implement default settings.
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return array(
      // Implement settings form.
    ) + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entity = $items->getEntity();
    $elements = [];

    $initial_data = [
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'bundle' => $entity->bundle(),
      'field_name' => $items->getFieldDefinition()->getName(),
      'uid' => \Drupal::currentUser()->id(),
    ];
    foreach ($items as $delta => $item) {
      $initial_data['likes'] = $items[$delta]->likes;
      $initial_data['dislikes'] = $items[$delta]->dislikes;
    }

    $data = json_encode($initial_data);
    $like_url = Url::fromRoute(
      'like_dislike.manager', ['clicked' => 'like', 'data' => $data]
    )->toString();
    $dislike_url = Url::fromRoute(
      'like_dislike.manager', ['clicked' => 'dislike', 'data' => $data]
    )->toString();

    $elements[] = [
      '#theme' => 'like_dislike',
      '#likes' => $initial_data['likes'],
      '#dislikes' => $initial_data['dislikes'],
      '#like_url' => $like_url,
      '#dislike_url' => $dislike_url,
    ];

    return $elements;
  }

}
