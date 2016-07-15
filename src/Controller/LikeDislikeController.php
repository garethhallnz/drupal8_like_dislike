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
use Drupal\Core\Link;
use Drupal\Core\Url;
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
      user_cookie_save(['destination' => $destination]);
      $options = array(
        'attributes' => array(
          'class' => array(
            'use-ajax',
            'login-popup-form',
          ),
          'data-dialog-type' => 'modal',
        ),
      );
      $user_register = Url::fromRoute('user.register')->setOptions($options);
      $user_login = Url::fromRoute('user.login')->setOptions($options);
      $register = Link::fromTextAndUrl(t('Register'), $user_register)->toString();
      $login = Link::fromTextAndUrl(t('Log in'), $user_login)->toString();
      $content = array(
        'content' => array(
          '#markup' => "Only logged in users are allowed to like/dislike. \n Visit ".$register ." | " . $login,
        ),
      );
      $user_login_register = \Drupal::service('renderer')->render($content);
      $dialog_library['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $response->setAttachments($dialog_library['#attached']);
      return $response->addCommand(new OpenModalDialogCommand('Like/Dislike', $user_login_register));
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
