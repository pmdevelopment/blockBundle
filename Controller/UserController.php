<?php

namespace Pluetzner\BlockBundle\Controller;

use Pluetzner\BlockBundle\Form\PasswordFormType;
use Pluetzner\BlockBundle\Form\UserFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 * @package Pluetzner\BlockBundle\Controller
 *
 * @Route("/admin/user")
 */
class UserController extends Controller
{
    /**
     * Index - List all Users
     *
     * @param Request $request
     * @return array
     * @Route("")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $checker = $this->get('security.authorization_checker');
        $qb = $this->getDoctrine()->getRepository(get_class($this->getUser()))->createQueryBuilder("u");
        $qb
            ->where("u.enabled = 1");

        $users = $qb->getQuery()->getResult();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $users,
            $request->get('page', 1),
            10
        ); //findBy([], ["name" => "ASC"]);

        return ["pagination" => $pagination];
    }

    /**
     * Detail
     *
     * @Route("/{id}/show")
     * @Route("/my-account")
     *
     * @Template()
     *
     * @param int $id
     * @return array
     */
    public function showAction($id = 0)
    {
        if (0 === $id) {
            $user = $this->getUser();
        } else {
            $user = $this->getDoctrine()->getRepository(get_class($this->getUser()))->find(intval($id));
        }

        return [
            "user" => $user,
        ];
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/create")
     * @Route("/{id}/edit")
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, $id = 0)
    {
        $checker = $this->get('security.authorization_checker');
        $userclass = get_class($this->getUser());
        if (0 < $id) {
            $user = $this->getDoctrine()->getRepository($userclass)->find(intval($id));
            if (null === $user) {
                throw $this->createNotFoundException();
            }
        } else {
            $user = new $userclass();
        }

        if (false === $checker->isGranted('ROLE_ADMIN')) {
            $user->setCompany($this->getUser()->getCompany());
        }
        $allRoles = array_keys($this->getParameter('security.role_hierarchy.roles'));
        $form = $this->createForm(UserFormType::class, $user, ['user' => $this->getUser(), 'checker' => $checker, 'roles' => $allRoles]);
        $form->handleRequest($request);

        if (true === $form->isValid()) {
            $check = null;
            if ($user->getId() === null) {
                $check = $this->getDoctrine()->getRepository($userclass)->findOneBy(['usernameCanonical' => $user->getUsernameCanonical()]);
            }
            if (null === $check) {
                $user->setUsername($user->getEmail());
                $manager = $this->getDoctrine()->getManager();

                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('pluetzner_block_user_index');
            }

            $form->addError(new FormError("Der Benutzername existiert bereits"));
        }

        return [
            "form" => $form->createView(),
            "user" => $user
        ];
    }

    /**
     * @param Request $request
     *
     * @Route("/change-password")
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function editPasswordAction(Request $request)
    {
        $user = $this->getDoctrine()->getRepository(get_class($this->getUser()))->find(intval($this->getUser()->getId()));

        if (null === $user || false === $user->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(PasswordFormType::class, $user);
        $form->handleRequest($request);

        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $check = null;
            if ($user->getId() === null) {
                $check = $this->getDoctrine()->getRepository(get_class($this->getUser()))->findOneBy(['usernameCanonical' => $user->getUsernameCanonical()]);
            }
            if (null === $check) {
                $manager = $this->getDoctrine()->getManager();

                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Password change successful');
                return $this->redirectToRoute("pluetzner_block_default_index");
            }

            $form->addError(new FormError("Der Benutzername existiert bereits"));
        }

        return [
            "form" => $form->createView(),
            "user" => $user
        ];
    }

    /**
     * @param int $id
     *
     * @Route("/{id}/delete")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $user = $this->getDoctrine()->getRepository(get_class($this->getUser()))->find(intval($id));
        if (null === $user || true === $user->isExpired()) {
            throw $this->createNotFoundException();
        }

        $user->setExpired(true);

        $manager = $this->getDoctrine()->getManager();

        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute("pluetzner_block_default_index");
    }

}