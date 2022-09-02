<?php

namespace App\Command;

use App\Entity\Newsletter;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\CourseRepository;
use App\Repository\NewsletterRepository;
use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;


class NewsLetterCommand extends Command
{

    const FROM_EMAIL = 'newsletter@tennis-school.com';

    protected static $defaultName = 'app:newsletter:send';

    private $teacherRepo;
    private $coursesRepo;
    private $articlesRepo;
    private $newsletterRepo;
    private $userRepo;
    private $mailer;

    public function __construct(
        TeacherRepository $teacherRepo,
        CourseRepository $courseRepository,
        ArticleRepository $articleRepository,
        NewsletterRepository $newsletterRepo,
        UserRepository $userRepo,
        MailerInterface $mailer)
    {
        $this->articlesRepo = $articleRepository;
        $this->teacherRepo = $teacherRepo;
        $this->coursesRepo = $courseRepository;
        $this->newsletterRepo = $newsletterRepo;
        $this->userRepo = $userRepo;

        $this->mailer = $mailer;

        parent::__construct();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $lastNewsletter = $this->newsletterRepo->findLatest();
        $searchItemsAfterDate = $lastNewsletter?->getCreatedAt();
        $newItems = $this->getNewItems($searchItemsAfterDate);
        if (!count($newItems->courses) && !count($newItems->articles) && !count($newItems->teachers)) {
            $output->writeln('No items to send newsletter for');
            return 0;
        }

        $now = new \DateTime();
        $recipients = $this->getRecipients();
        $newsletter = (new Newsletter())->setTitle(
            'Newsletter ' . $now->format('d/m/Y')
        )->setRecipients($recipients);

        $templateEmail = (new TemplatedEmail())
            ->subject($newsletter->getTitle())
            ->from(self::FROM_EMAIL)
            ->htmlTemplate('newsletter/index.html.twig')
            ->addTo(...$recipients)
            ->context([
                'items' => $newItems,
                'after' => $searchItemsAfterDate,
                'newsletter' => $newsletter
            ]);


        $this->mailer->send($templateEmail);
        $newsletter->setText('');
        $this->newsletterRepo->add($newsletter, true);


        return 0;
    }

    private function getNewItems(\DateTime $date = null): NewsLetterItems
    {
        $newItems = new NewsLetterItems();
        $newItems->articles = $this->articlesRepo->getForNewsLetter($date);
        $newItems->teachers = $this->teacherRepo->getForNewsLetter($date, true);
        $newItems->courses = $this->coursesRepo->getForNewsLetter($date, true);

        return $newItems;
    }

    private function getRecipients(): array
    {
        $users = array_filter($this->userRepo->findAll(), function (User $user) {
            return in_array(User::ROLE_USER, $user->getRoles());
        });

        return array_map(function (User $user){ return $user->getEmail(); }, $users);
    }
}