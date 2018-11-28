<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EmailConfigType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EmailConfigType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $smtp = $builder
            ->create('smtp', null, [
                'required' => false,
                'compound' => true,
            ])
            ->add('host', Type\TextType::class, [
                'required' => true,
            ])
            ->add('port', Type\IntegerType::class, [
                'required' => true,
            ])
            ->add('encryption', Type\ChoiceType::class, [
                'choices'  => [
                    'TLS'  => 'tls',
                    'SSL'  => 'ssl',
                    'None' => null,
                ],
                'required' => true,
            ])
            ->add('auth_mode', Type\ChoiceType::class, [
                'choices'  => [
                    'Login'    => 'login',
                    'Plain'    => 'plain',
                    'CRAM-MD5' => 'cram-md5',
                    'None'     => null,
                ],
                'required' => true,
            ])
            ->add('username', Type\TextType::class)
            ->add('password', Type\PasswordType::class, [
                'always_empty' => false,
            ]);

        $imap = $builder
            ->create('imap', null, [
                'required' => false,
                'compound' => true,
            ])
            ->add('mailbox', Type\TextType::class, [
                'required' => true,
            ])
            ->add('folder', Type\TextType::class, [
                'required' => true,
            ])
            ->add('user', Type\TextType::class, [
                'required' => true,
            ])
            ->add('password', Type\PasswordType::class, [
                'always_empty' => false,
            ]);

        $builder
            ->add($smtp)
            ->add($imap)
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                $smtp = $data['smtp'];
                if (empty($smtp['host']) && empty($smtp['port'])) {
                    unset($data['smtp']);
                }

                $imap = $data['imap'];
                if (empty($imap['mailbox']) && empty($imap['folder']) && empty($imap['user']) && empty($imap['password'])) {
                    unset($data['imap']);
                }

                if (empty($data['smtp']) && empty($data['imap'])) {
                    $data = null;
                }

                $event->setData($data);
            }, 2048);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_admin_email_config';
    }
}