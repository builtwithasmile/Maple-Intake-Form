<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_swens_mi
 *
 * SWENS_MI — Vanilla secure intake form module for Joomla 6.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

require_once __DIR__ . '/helper.php';

$app   = Factory::getApplication();
$input = $app->getInput();

$errors  = [];
$success = false;

// Handle POST
if ($input->getMethod() === 'POST' && $input->post->getString('SWENS_MI_FORM') === '1')
{
    // CSRF
    if (!Session::checkToken('post'))
    {
        $errors[] = Text::_('SWENS_MI_ERR_TOKEN');
    }

    // Basic rate limit (session)
    $minSeconds = (int) $params->get('rate_limit_seconds', 10);
    $last = (int) $app->getSession()->get('SWENS_MI_LAST_SUBMIT_TS', 0);
    $now  = time();

    if (!$errors && $last > 0 && ($now - $last) < $minSeconds)
    {
        $errors[] = Text::sprintf('SWENS_MI_ERR_RATE', $minSeconds);
    }

    // Honeypot
    $hp = $input->post->getString('website', '');
    if (!$errors && $hp !== '')
    {
        // silently treat as success to avoid probing
        $success = true;
    }

    // CAPTCHA (optional; uses Joomla global captcha provider)
    if (!$errors && !$success && (int) $params->get('use_captcha', 0) === 1)
    {
        $captcha = \Joomla\CMS\Captcha\Captcha::getInstance($app->get('captcha', 'recaptcha'), ['namespace' => 'swens_mi']);
        if ($captcha)
        {
            $captchaInput = $input->post->get('g-recaptcha-response', '', 'raw');
            if (!$captcha->checkAnswer($captchaInput))
            {
                $errors[] = Text::_('SWENS_MI_ERR_CAPTCHA');
            }
        }
    }

    // Validate + send
    if (!$errors && !$success)
    {
        $data = modSwensMiHelper::collectAndValidate($input, $params, $errors);

        if (!$errors)
        {
            $ok = modSwensMiHelper::sendEmail($data, $params, $errors);
            if ($ok)
            {
                $app->getSession()->set('SWENS_MI_LAST_SUBMIT_TS', $now);
                $success = true;
            }
        }
    }

    // Redirect on success (preferred for ads/conversions)
    if ($success)
    {
        $redirect = trim((string) $params->get('redirect_url', ''));

        // Allow relative URLs only (security)
        if ($redirect !== '' && preg_match('#^https?://#i', $redirect))
        {
            $redirect = '';
        }

        if ($redirect !== '')
        {
            $app->redirect(Route::_($redirect, false));
            return;
        }
    }
}

require ModuleHelper::getLayoutPath('mod_swens_mi', $params->get('layout', 'default'));
