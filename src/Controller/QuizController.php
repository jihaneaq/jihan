<?php

namespace App\Controller;

use App\Entity\Score;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class QuizController extends AbstractController
{
    /**
     * Page d'accueil
     * CHANGEMENT : Nom de la route harmonisé avec tes fichiers Twig
     */
    #[Route('/', name: 'app_quiz_home', methods: ['GET'])]
    public function home(QuestionRepository $questionRepository): Response
    {
        return $this->render('quiz/home.html.twig', [
            'totalQuestions' => count($questionRepository->findAll()),
        ]);
    }

    /**
     * Affichage du quiz
     */
    #[IsGranted('ROLE_USER')] // Réactivé : protection contre les accès anonymes
    #[Route('/quiz', name: 'quiz_index', methods: ['GET'])]
    public function index(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();

        return $this->render('quiz/index.html.twig', [
            'questions' => $questions,
        ]);
    }

    /**
     * Traitement du score
     */
    #[IsGranted('ROLE_USER')] // Réactivé
    #[Route('/quiz/result', name: 'quiz_result', methods: ['POST'])]
    public function result(
        Request $request,
        QuestionRepository $questionRepository,
        EntityManagerInterface $em
    ): Response {
        $scoreValue = 0;
        $questions = $questionRepository->findAll();
        $correction = [];

        foreach ($questions as $question) {
            $reponseUtilisateur = $request->request->get('q' . $question->getId());
            $bonneReponse = $question->getReponse();
            $estCorrect = ($reponseUtilisateur === $bonneReponse);

            if ($estCorrect) {
                $scoreValue++;
            }

            $correction[] = [
                'question' => $question->getQuestion(),
                'reponseUtilisateur' => $reponseUtilisateur,
                'bonneReponse' => $bonneReponse,
                'estCorrect' => $estCorrect
            ];
        }

        // --- SAUVEGARDE LIÉE À L'USER ---
        $scoreEntity = new Score();
        $scoreEntity->setScore($scoreValue);
        $scoreEntity->setCreatedAt(new \DateTime());
        
        // On vérifie qu'un utilisateur est bien connecté avant d'associer
        if ($this->getUser()) {
            $scoreEntity->setUser($this->getUser());
            $em->persist($scoreEntity);
            $em->flush();
        }

        return $this->render('quiz/result.html.twig', [
            'score' => $scoreValue,
            'total' => count($questions),
            'correction' => $correction 
        ]);
    }
}