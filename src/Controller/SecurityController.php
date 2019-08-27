<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegisterFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function register(Request $request): Response
    {
        $user = new User();
        $error = null;
        $success = null;

        $form = $this->createForm(UserRegisterFormType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $formData = $form->getData();
            $activationToken = md5(time());

            $user->setEmail($formData['email']);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $this->email,
                $formData['password']
            ));
            $user->setIsActive(0);
            $user->setActivationToken($activationToken);

            $em->persist($user);
            $em->flush();

            return new RedirectToRoute($this->urlGenerator->generate('app_register_success'), [
                'activationToken' => $activationToken
            ]);
        } else {
            return $this->render('security/register.html.twig',
                [
                    'form' => $form->createView(),
                    'error' => $error,
                ]
            );
        }
    }

    /**
     * @Route("/uspesna-registrace/{activationToken}", name="app_register_success")
     */
    public function registerSuccess(string $activationToken = null): Response
    {
        $error = false;
        $success = false;
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findBy([
            'activationToken' => $activationToken,
        ]);

        if($user) {
            $success = true;
        } else {
            $error = true;
        }

        return $this->render('security/register_success.html.twig',
            [
                'registerSuccess' => $success,
                'registerError' => $error
            ]);
    }

    /**
     * @Route("/odhlaseni", name="app_logout")
     */
    public function logout()
    {
        return new RedirectResponse($this->urlGenerator->generate('app_loign'));

        //throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
