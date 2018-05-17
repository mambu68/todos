<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Todos;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Todo controller.
 *
 * @Route("todos")
 */
class TodosController extends Controller
{
    /**
     * Lists all todo entities.
     *
     * @Route("/", name="todos_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $securityContext = $this->get('security.authorization_checker');
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($this->getUser());

        $todos = $em->getRepository('AppBundle:Todos')->findBy(['userID' => $user->getId()]);

        if($request->isXmlHttpRequest()) {
            $normalizer = new ObjectNormalizer();
            $normalizer->setCircularReferenceLimit(2);
            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });
            $encoders = [ new JsonEncoder() ];
            $normalizers = [ $normalizer ];
            $serializer = new Serializer($normalizers, $encoders);
            $jsonContent = $serializer->serialize($todos, 'json');

            return new Response($jsonContent);
        } else {
            return $this->render('@App/todos/index.html.twig', array(
                'todos' => $todos,
            ));
        }
    }

    /**
     * Creates a new todo entity.
     *
     * @Route("/new", name="todos_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $securityContext = $this->get('security.authorization_checker');
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($this->getUser());

        $todo = new Todos();
        $form = $this->createForm('AppBundle\Form\TodosType', $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $todo->setUserID($user->getId());
            // $todo->setCatId( $form->getData()->getCategory()->getId() );
            $em->persist($todo);
            $em->flush();

            return $this->redirectToRoute('todos_show', array('id' => $todo->getId()));
        }

        return $this->render('@App/todos/new.html.twig', array(
            'todo' => $todo,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a todo entity.
     *
     * @Route("/{id}", name="todos_show")
     * @Method("GET")
     */
    public function showAction(Todos $todo)
    {
        $deleteForm = $this->createDeleteForm($todo);

        return $this->render('@App/todos/show.html.twig', array(
            'todo' => $todo,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing todo entity.
     *
     * @Route("/{id}/edit", name="todos_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Todos $todo)
    {
        // $deleteForm = $this->createDeleteForm($todo);
        $editForm = $this->createForm('AppBundle\Form\TodosType', $todo);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('todos_edit', array('id' => $todo->getId()));
        }

        return $this->render('@App/todos/edit.html.twig', array(
            'todo' => $todo,
            'edit_form' => $editForm->createView()
            // 'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a todo entity.
     *
     * @Route("/{id}/delete", name="todos_delete")
     */
    public function deleteAction(Request $request, Todos $todo)
    {
        // $form = $this->createDeleteForm($todo);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($todo);
            $em->flush();
        // }

        return $this->redirectToRoute('todos_index');
    }

    /**
     * Creates a form to delete a todo entity.
     *
     * @param Todos $todo The todo entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Todos $todo)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('todos_delete', array('id' => $todo->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
