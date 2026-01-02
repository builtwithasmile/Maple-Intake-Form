<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Uri\Uri;

class modSwensMiHelper
{
    public static function collectAndValidate($input, $params, &$errors)
    {
        $data = [];

        // Core intake fields (vanilla, stable)
        $fields = [
            'property_name'     => ['label' => Text::_('SWENS_MI_F_PROPERTY_NAME'), 'type' => 'string', 'required' => true,  'max' => 120],
            'property_type'     => ['label' => Text::_('SWENS_MI_F_PROPERTY_TYPE'), 'type' => 'string', 'required' => true,  'max' => 40],
            'location'          => ['label' => Text::_('SWENS_MI_F_LOCATION'),      'type' => 'string', 'required' => true,  'max' => 120],
            'device_count'      => ['label' => Text::_('SWENS_MI_F_DEVICE_COUNT'),  'type' => 'int',    'required' => true,  'min' => 1, 'max' => 999],
            'internet_provider' => ['label' => Text::_('SWENS_MI_F_ISP'),           'type' => 'string', 'required' => true,  'max' => 80],
            'pain_points'       => ['label' => Text::_('SWENS_MI_F_PAIN_POINTS'),   'type' => 'string', 'required' => true,  'max' => 2000],
            'contact_name'      => ['label' => Text::_('SWENS_MI_F_CONTACT_NAME'),  'type' => 'string', 'required' => true,  'max' => 120],
            'contact_email'     => ['label' => Text::_('SWENS_MI_F_CONTACT_EMAIL'), 'type' => 'email',  'required' => true,  'max' => 190],
            'contact_phone'     => ['label' => Text::_('SWENS_MI_F_CONTACT_PHONE'), 'type' => 'string', 'required' => true,  'max' => 40],
            'best_time'         => ['label' => Text::_('SWENS_MI_F_BEST_TIME'),     'type' => 'string', 'required' => true,  'max' => 40],
        ];

        foreach ($fields as $name => $cfg)
        {
            $raw = $input->post->get($name, '', 'raw');
            $val = is_string($raw) ? trim($raw) : $raw;

            if ($cfg['required'] && ($val === '' || $val === null))
            {
                $errors[] = Text::sprintf('SWENS_MI_ERR_REQUIRED', $cfg['label']);
                continue;
            }

            if ($cfg['type'] === 'int')
            {
                $ival = (int) $val;
                if ($cfg['required'] && $ival < (int) $cfg['min'])
                {
                    $errors[] = Text::sprintf('SWENS_MI_ERR_MIN', $cfg['label'], (int) $cfg['min']);
                    continue;
                }
                if ($ival > (int) $cfg['max'])
                {
                    $errors[] = Text::sprintf('SWENS_MI_ERR_MAX', $cfg['label'], (int) $cfg['max']);
                    continue;
                }
                $data[$name] = $ival;
                continue;
            }

            // String/Email
            if (is_string($val) && isset($cfg['max']) && mb_strlen($val) > (int) $cfg['max'])
            {
                $errors[] = Text::sprintf('SWENS_MI_ERR_LEN', $cfg['label'], (int) $cfg['max']);
                continue;
            }

            if ($cfg['type'] === 'email' && !MailHelper::isEmailAddress($val))
            {
                $errors[] = Text::sprintf('SWENS_MI_ERR_EMAIL', $cfg['label']);
                continue;
            }

            // Remove control chars
            $data[$name] = preg_replace('/[[:cntrl:]]/', '', (string) $val);
        }

        $data['submitted_at'] = gmdate('c');
        $data['page'] = Uri::getInstance()->toString();

        return $data;
    }

    public static function sendEmail($data, $params, &$errors)
    {
        $app = Factory::getApplication();
        $mailer = Factory::getMailer();

        $to = trim((string) $params->get('recipient_email', ''));
        if ($to === '' || !MailHelper::isEmailAddress($to))
        {
            $errors[] = Text::_('SWENS_MI_ERR_RECIPIENT');
            return false;
        }

        $subjectPrefix = trim((string) $params->get('subject_prefix', 'SWENS_MI Intake'));
        $subject = $subjectPrefix . ' — ' . ($data['property_name'] ?: 'Property');

        $lines = [];
        $lines[] = 'SWENS_MI — New intake submission';
        $lines[] = '----------------------------------------';
        $lines[] = 'Property Name: ' . $data['property_name'];
        $lines[] = 'Property Type: ' . $data['property_type'];
        $lines[] = 'Location: ' . $data['location'];
        $lines[] = 'Device Count: ' . $data['device_count'];
        $lines[] = 'Internet Provider: ' . $data['internet_provider'];
        $lines[] = 'Primary IT Issues: ' . $data['pain_points'];
        $lines[] = '----------------------------------------';
        $lines[] = 'Contact Name: ' . $data['contact_name'];
        $lines[] = 'Contact Email: ' . $data['contact_email'];
        $lines[] = 'Contact Phone: ' . $data['contact_phone'];
        $lines[] = 'Best Time: ' . $data['best_time'];
        $lines[] = '----------------------------------------';
        $lines[] = 'Submitted At (UTC): ' . $data['submitted_at'];
        $lines[] = 'Page: ' . $data['page'];
        $body = implode("\n", $lines);

        try
        {
            $mailer->addRecipient($to);
            $mailer->setSubject($subject);
            $mailer->setBody($body);
            $mailer->addReplyTo($data['contact_email'], $data['contact_name']);

            $sent = $mailer->Send();
            if ($sent !== true)
            {
                $errors[] = Text::_('SWENS_MI_ERR_SEND');
                return false;
            }

            // Optional: confirmation email to submitter
            if ((int) $params->get('send_autoreply', 0) === 1)
            {
                $fromEmail = $app->get('mailfrom');
                $fromName  = $app->get('fromname');

                $auto = Factory::getMailer();
                $auto->addRecipient($data['contact_email']);
                $auto->setSubject($subjectPrefix . ' — ' . Text::_('SWENS_MI_AUTOREPLY_SUBJECT'));
                $autoBody = trim((string) $params->get('autoreply_body', Text::_('SWENS_MI_AUTOREPLY_BODY')));
                $auto->setBody($autoBody);
                $auto->setSender([$fromEmail, $fromName]);
                $auto->Send();
            }

            return true;
        }
        catch (\Throwable $e)
        {
            $errors[] = Text::_('SWENS_MI_ERR_SEND');
            return false;
        }
    }
}
