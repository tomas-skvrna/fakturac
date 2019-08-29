<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegisterFormType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    protected $passwordEncoder;

    /**
     * SecurityController constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/prihlaseni", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->redirectToRoute('homepage');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]);
    }

    /**
     * @Route("/registrace", name="app_register")
     */
    public function register(
        Request $request,
        MailerInterface $mailer): Response
    {
        $user = new User();
        $registerError = null;
        $success = null;

        $form = $this->createForm(UserRegisterFormType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            try {
                $em = $this->getDoctrine()->getManager();
                $activationToken = md5(time());

                $user = $formData;
                $user->setRoles(['ROLE_USER']);
                $user->setPassword($this->passwordEncoder->encodePassword(
                    $formData,
                    $formData->getPassword()
                ));
                $user->setIsActive(0);
                $user->setActivationToken($activationToken);

                $em->persist($user);
                $em->flush();

                // odeslání e-mailu
                $email = (new TemplatedEmail())
                    ->from('info@fakturac.cz')
                    ->to($user->getEmail())
                    ->subject('Fakturáč - aktivační kód')
                    ->htmlTemplate('_email/activate_user.html.twig')
                    ->context([
                        'activationToken' => $activationToken
                    ]);

                $mailer->send($email);

                return $this->redirectToRoute('app_register_success', [
                    'activationToken' => $activationToken
                ]);
            } catch (UniqueConstraintViolationException $ex) {
                $registerError = 'user_already_registered';
            }
        }

        return $this->render('security/register.html.twig',
            [
                'form' => $form->createView(),
                'registerError' => $registerError,
            ]
        );
    }

    /**
     * @Route("/dokonceni-registrace/{activationToken}", name="app_register_success")
     */
    public function registerSuccess(
        string $activationToken = null,
        MailerInterface $mailer): Response
    {
        $error = false;
        $success = false;
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy([
            'activationToken' => $activationToken,
        ]);

        if($user) {
            $success = true;
        } else {
            $error = true;
        }

        // odeslání e-mailu
        $email = (new TemplatedEmail())
            ->from('info@fakturac.cz')
            ->to($user->getEmail())
            ->subject('Fakturáč - aktivační kód')
            ->htmlTemplate('_email/activate_user.html.twig')
            ->context([
                'activationToken' => $activationToken
            ]);

        $mailer->send($email);

        return $this->render('security/register_success.html.twig',
            [
                'registerSuccess' => $success,
                'registerError' => $error
            ]);
    }

    public function activate(
        string $activationToken = null,
        MailerInterface $mailer): Response
    {
        $error = false;
        $success = false;
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy([
            'activationToken' => $activationToken,
        ]);

        if($user) {
            $em = $this->getDoctrine()->getManager();
            $user->setIsActivated(1);
            $user->setActivationToken(null);
            $em->persist($user);
            $em->flush();

            // odeslání e-mailu
            $email = (new TemplatedEmail())
                ->from('info@fakturac.cz')
                ->to($user->getEmail())
                ->subject('Fakturáč - aktivační kód')
                ->htmlTemplate('_email/welcome_user.html.twig');

            $mailer->send($email);

            $success = true;
        } else {
            $error = true;
        }

        return $this->render('security/activate.html.twig',
            [
                'activateSuccess' => $success,
                'activateError' => $error
            ]);
    }

    /**
     * @Route("/odhlaseni", name="app_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('app_login');

        //throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
