<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$action      = Uri::getInstance()->toString(['path', 'query']);
$redirect    = trim((string) $params->get('redirect_url', ''));
$showCaptcha = (int) $params->get('use_captcha', 0) === 1;

$val = function (string $key): string {
    return isset($_POST[$key]) ? htmlspecialchars((string) $_POST[$key], ENT_QUOTES, 'UTF-8') : '';
};
$sel = function (string $key, string $value): string {
    return (isset($_POST[$key]) && (string) $_POST[$key] === $value) ? 'selected' : '';
};
?>

<section class="intake-form-section">
  <div class="container">

    <!-- Form Header -->
    <div class="form-header">
      <h2>Let's understand your setup</h2>
      <p>Answer a few quick questions so we can design a plan that actually fits your situation.</p>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)) : ?>
      <div class="form-message error" role="alert">
        <strong>Please fix these issues:</strong>
        <ul>
          <?php foreach ($errors as $err) : ?>
            <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if ($success) : ?>
      <div class="form-message success" role="status">
        <div>✓</div>
        <h2>Got it!</h2>
        <p class="success-message">We received your intake and will review it within 24 hours. We'll reach out to clarify any details and start designing a plan for you.</p>
        <?php if ($redirect !== '') : ?>
          <a class="btn-primary" href="<?php echo Route::_($redirect); ?>">Back to Home</a>
        <?php endif; ?>
      </div>
    <?php else : ?>

      <form class="intake-form" method="post" action="<?php echo htmlspecialchars($action, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="SWENS_MI_FORM" value="1">

        <!-- Honeypot -->
        <div style="position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden;">
          <label for="SWENS_MI-website">Website</label>
          <input id="SWENS_MI-website" type="text" name="website" value="" autocomplete="off" tabindex="-1">
        </div>

        <!-- Section 1: Property Basics -->
        <fieldset class="form-section">
          <legend>Property Basics</legend>

          <div class="form-group">
            <label for="SWENS_MI-property-name">
              Property Name <span>*</span>
            </label>
            <input
              id="SWENS_MI-property-name"
              name="property_name"
              type="text"
              required
              maxlength="120"
              value="<?php echo $val('property_name'); ?>"
              placeholder="e.g., Casa Blanca, Sunset Lodge"
            >
          </div>

          <div class="form-group">
            <label for="SWENS_MI-property-type">
              Property Type <span>*</span>
            </label>
            <select id="SWENS_MI-property-type" name="property_type" required>
              <option value="">- Select -</option>
              <option value="home_condo" <?php echo $sel('property_type', 'home_condo'); ?>>Home or Condo</option>
              <option value="rental" <?php echo $sel('property_type', 'rental'); ?>>Rental Property</option>
              <option value="small_hotel" <?php echo $sel('property_type', 'small_hotel'); ?>>Small Hotel</option>
              <option value="lodge_resort" <?php echo $sel('property_type', 'lodge_resort'); ?>>Lodge or Resort</option>
              <option value="other" <?php echo $sel('property_type', 'other'); ?>>Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="SWENS_MI-location">
              Location in Guanacaste <span>*</span>
            </label>
            <input
              id="SWENS_MI-location"
              name="location"
              type="text"
              required
              maxlength="120"
              value="<?php echo $val('location'); ?>"
              placeholder="e.g., Liberia, Playas del Coco, Tamarindo"
            >
          </div>
        </fieldset>

        <!-- Section 2: Current Setup -->
        <fieldset class="form-section">
          <legend>Your Current Setup</legend>

          <div class="form-group">
            <label for="SWENS_MI-device-count">
              How many devices? <span>*</span>
            </label>
            <input
              id="SWENS_MI-device-count"
              name="device_count"
              type="number"
              min="1"
              max="999"
              required
              inputmode="numeric"
              value="<?php echo $val('device_count'); ?>"
              placeholder="Rough count: laptops, desktops, tablets"
            >
          </div>

          <div class="form-group">
            <label for="SWENS_MI-isp">
              Internet Provider <span>*</span>
            </label>
            <input
              id="SWENS_MI-isp"
              name="internet_provider"
              type="text"
              required
              maxlength="80"
              value="<?php echo $val('internet_provider'); ?>"
              placeholder="Kolbi/ICE, Claro, Cabletica, Starlink"
            >
          </div>
        </fieldset>

        <!-- Section 3: Pain Points -->
        <fieldset class="form-section">
          <legend>What's Breaking?</legend>

          <div class="form-group">
            <label for="SWENS_MI-pain">
              Tell us what needs to get better <span>*</span>
            </label>
            <textarea
              id="SWENS_MI-pain"
              name="pain_points"
              rows="4"
              required
              maxlength="2000"
              placeholder="Wi-Fi drops, slow internet, guest complaints, camera issues, security concerns…"
            ><?php echo $val('pain_points'); ?></textarea>
          </div>
        </fieldset>

        <!-- Section 4: Contact Info -->
        <fieldset class="form-section">
          <legend>How to Reach You</legend>

          <div class="form-group">
            <label for="SWENS_MI-contact-name">
              Your Name <span>*</span>
            </label>
            <input
              id="SWENS_MI-contact-name"
              name="contact_name"
              type="text"
              required
              maxlength="120"
              value="<?php echo $val('contact_name'); ?>"
              placeholder="First and last name"
              autocomplete="name"
            >
          </div>

          <div class="form-group">
            <label for="SWENS_MI-contact-email">
              Email <span>*</span>
            </label>
            <input
              id="SWENS_MI-contact-email"
              name="contact_email"
              type="email"
              required
              maxlength="190"
              value="<?php echo $val('contact_email'); ?>"
              placeholder="your@email.com"
              autocomplete="email"
            >
          </div>

          <div class="form-group">
            <label for="SWENS_MI-contact-phone">
              Phone <span>*</span>
            </label>
            <input
              id="SWENS_MI-contact-phone"
              name="contact_phone"
              type="tel"
              required
              maxlength="40"
              value="<?php echo $val('contact_phone'); ?>"
              placeholder="+506 XXXX XXXX"
              autocomplete="tel"
            >
          </div>

          <div class="form-group">
            <label for="SWENS_MI-best-time">
              Best time to reach you <span>*</span>
            </label>
            <select id="SWENS_MI-best-time" name="best_time" required>
              <option value="">- Select -</option>
              <option value="morning" <?php echo $sel('best_time', 'morning'); ?>>Morning</option>
              <option value="afternoon" <?php echo $sel('best_time', 'afternoon'); ?>>Afternoon</option>
              <option value="evening" <?php echo $sel('best_time', 'evening'); ?>>Evening</option>
              <option value="no_pref" <?php echo $sel('best_time', 'no_pref'); ?>>No preference</option>
            </select>
          </div>
        </fieldset>

        <!-- Captcha -->
        <?php if ($showCaptcha) : ?>
          <fieldset class="form-section">
            <legend>Verification</legend>
            <div class="form-group">
              <?php
                $app = \Joomla\CMS\Factory::getApplication();
                $captcha = \Joomla\CMS\Captcha\Captcha::getInstance($app->get('captcha', 'recaptcha'), ['namespace' => 'swens_mi']);
                if ($captcha) { echo $captcha->display('g-recaptcha-response', 'swens_mi_captcha'); }
              ?>
            </div>
          </fieldset>
        <?php endif; ?>

        <!-- Submit Button -->
        <div class="form-submit">
          <button type="submit" class="btn-primary">Send My Intake</button>
          <p class="form-note">We'll review this and reach out within 24 hours.</p>
        </div>

        <!-- Joomla CSRF token -->
        <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1">
      </form>

    <?php endif; ?>

  </div>
</section>

