<?php
namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/machines', name: 'admin_machines_')]
class AdminMachineController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(MachineRepository $machineRepository): Response
    {
        $machines = $machineRepository->findAll();

        return $this->render('admin/machine/index.html.twig', [
            'machines' => $machines,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $machine = new Machine();
        $form = $this->createForm(MachineType::class, $machine);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($machine);
            $em->flush();

            return $this->redirectToRoute('admin_machines_index');
        }

        return $this->render('admin/machine/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Machine $machine, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_machines_index');
        }

        return $this->render('admin/machine/edit.html.twig', [
            'form' => $form->createView(),
            'machine' => $machine,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Machine $machine, EntityManagerInterface $em): Response
    {
        $em->remove($machine);
        $em->flush();

        return $this->redirectToRoute('admin_machines_index');
    }
}
