<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Base\BaseController;

class DefaultController extends BaseController
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->getUser();

        // Family list
        $dql = "SELECT f.id, f.name as name, f.active as active, count(p.id) as nbProjects, ug.nickname as username, ug.id as user_id
                    FROM AppBundle:Family f
                    LEFT JOIN AppBundle:Project p
                    WITH p.family = f.id
                    LEFT JOIN AppBundle\Entity\UserGoogle ug
                    WITH f.user = ug.id
                    GROUP BY f.name
                    ORDER BY f.name
                    ";
        $families = $this
            ->get("doctrine")
            ->getEntityManager()
            ->createQuery($dql)
            ->getResult();
//var_dump($families);
        // First list with only project before end date
        $dql = "SELECT p FROM AppBundle:Project p WHERE p.endDate >= :endDate ORDER BY p.endDate ASC";
        $projects = $this
            ->get("doctrine")
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameter("endDate", date("Y-m-d H:i:s", time()))
            ->getResult();

        foreach ($projects as $oneProject) {
            $step_list = $this->get("doctrine")->getRepository("AppBundle:Step")->findBy(['project_id' => $oneProject->getId()]);
            $all_credits = $this->get("doctrine")->getRepository("AppBundle:CreditsHistory")->findBy(['project' => $oneProject]);
            $oneProject->setStepsAndCredits($step_list, $all_credits);
            // Show participants
            $participants = $this->get("doctrine")->getRepository("AppBundle:Project")->participants($oneProject);
            $oneProject->setParticipants($participants);
        }

        // Old projects (date < now)
        $dql_old = "SELECT p FROM AppBundle:Project p WHERE p.endDate < :endDate ORDER BY p.endDate ASC";
        $old_projects = $this
            ->get("doctrine")
            ->getEntityManager()
            ->createQuery($dql_old)
            ->setParameter("endDate", date("Y-m-d H:i:s", time()))
            ->getResult();

        foreach ($old_projects as $oneProject) {
            $step_list = $this->get("doctrine")->getRepository("AppBundle:Step")->findBy(['project_id' => $oneProject->getId()]);
            $all_credits = $this->get("doctrine")->getRepository("AppBundle:CreditsHistory")->findBy(['project' => $oneProject]);
            $oneProject->setStepsAndCredits($step_list, $all_credits);
        }

        return array(
            'menu_hp' => 'active',
            'projects' => $projects,
            'old_projects' => $old_projects,
            'families' => $families,
            'user' => $user,
        );
    }
}
