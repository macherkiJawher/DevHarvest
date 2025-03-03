<?php

namespace App\Controller;

use App\Entity\Zone;
use App\Form\ZoneType;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/zone')]
final class ZoneController extends AbstractController
{
    private $slugger;

   
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    #[Route(name: 'app_zone_index', methods: ['GET'])]
    public function index(ZoneRepository $zoneRepository): Response
    {
        return $this->render('zone/index.html.twig', [
            'zones' => $zoneRepository->findAll(),
        ]);
    }

    // src/Controller/ZoneController.php

#[Route('/new', name: 'app_zone_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $zone = new Zone();
    $form = $this->createForm(ZoneType::class, $zone);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('zones_directory'), 
                    $newFilename
                );
                $zone->setImage($newFilename);
            } catch (FileException $e) {
                // Handle error
            }
        }

        // Enregistrer la latitude et la longitude dans l'entité
        $localisation = $form->get('localisation_zone')->getData();
        if ($localisation) {
            list($longitude, $latitude) = explode(',', $localisation);
            $zone->setLongitude((float) $longitude);
            $zone->setLatitude((float) $latitude);
        }

        $entityManager->persist($zone);
        $entityManager->flush();


        $this->addFlash('success', 'zone ajoutée avec succès !');
        return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
    }

    $mapboxApiKey = $this->getParameter('mapbox_api_key');

    return $this->render('zone/new.html.twig', [
        'zone' => $zone,
        'form' => $form,
        'mapbox_api_key' => $mapboxApiKey,
    ]);
}


    #[Route('/{id}', name: 'app_zone_show', methods: ['GET'])]
    public function show(Zone $zone): Response
    {
        $mapboxApiKey = $this->getParameter('mapbox_api_key');
        return $this->render('zone/show.html.twig', [
            'zone' => $zone,
            'granges' => $zone->getGranges(),
            'mapbox_api_key' => $this->getParameter('mapbox_api_key'),
            
        ]);
    }

    #[Route('/{id}/edit', name: 'app_zone_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Zone $zone, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ZoneType::class, $zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
        }
        $mapboxApiKey = $this->getParameter('mapbox_api_key');

        $this->addFlash('success', 'zone modifié avec succès !');
        return $this->render('zone/edit.html.twig', [
            'zone' => $zone,
            'form' => $form,
            'mapbox_api_key' => $mapboxApiKey,
        ]);
    }

    #[Route('/{id}', name: 'app_zone_delete', methods: ['POST'])]
    public function delete(Request $request, Zone $zone, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$zone->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($zone);
                $entityManager->flush();
                $this->addFlash('success', 'Zone supprimée avec succès.');
            } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
    
                $this->addFlash('danger', 'Impossible de supprimer cette zone car elle est liée à une ou plusieurs granges.');
            }
        }
        $this->addFlash('success', 'zone supprimé avec succès !');
        return $this->redirectToRoute('app_zone_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/zones', name: 'zone_recherche', methods: ['GET'])]
public function searchZones(Request $request, ZoneRepository $zoneRepository): Response
{
    // Récupérer les paramètres de la requête GET
    $query = $request->query->get('search', '');
    $superficie = $request->query->get('superficie', null); // Si tu veux aussi filtrer par superficie
    $nomZone = $request->query->get('nom_zone', null);
    $localisationZone = $request->query->get('localisation_zone', null);

    // Appeler la méthode de recherche avancée
    $zones = $zoneRepository->advancedSearch($query, $superficie, $nomZone, $localisationZone);

    return $this->render('zone/recherche.html.twig', [
        'zones' => $zones,
        'search' => $query,
        'superficie' => $superficie,
        'nom_zone' => $nomZone,
        'localisation_zone' => $localisationZone
    ]);
}


}