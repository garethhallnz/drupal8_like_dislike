<?php

/**
 * @file
 * Contains \Drupal\like_dislike\Controller\LikeDislikeController.
 */

namespace Drupal\like_dislike\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class LikeDislikeController.
 *
 * @package Drupal\like_dislike\Controller
 */
class LikeDislikeController extends ControllerBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs an LinkClickCountController object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request.
   */
  public function __construct(RequestStack $request) {
    $this->requestStack = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

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
    if ($user == 0) {
      $destination = $this->requestStack->getCurrentRequest()->get('like-dislike-redirect');

      $content = array(
        'content' => array(
          '#markup' => 'My return, After login go back to this url ' . $destination,
        ),
      );
      $html = \Drupal::service('renderer')->render($content);
      $dialog_library['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $response->setAttachments($dialog_library['#attached']);
      return $response->addCommand(new OpenModalDialogCommand('hi', $html));
    }

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
