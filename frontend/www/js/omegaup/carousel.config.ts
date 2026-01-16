const carouselConfig: {
  image: string;
  title: {
    en: string;
    es: string;
    pt: string;
  };
  description: {
    en: string;
    es: string;
    pt: string;
  };
  button?: {
    text: {
      en: string;
      es: string;
      pt: string;
    };
    href: string;
    target: string;
  };
}[] = [
  {
    image: '/media/homepage/cursoofmi.png',
    title: {
      en: 'OFMI on YouTube',
      es: 'OFMI en YouTube',
      pt: 'OFMI no YouTube',
    },
    description: {
      en: 'Holidays can also be a great time to learn at your own pace. If you want to keep training your mind and reinforce concepts, we invite you to check out our course. Learn, explore, and enjoy the process. Knowledge doesn’t take a vacation.',
      es: 'Las vacaciones también pueden ser un buen momento para aprender a tu ritmo. Si quieres seguir entrenando tu mente y reforzar conceptos, te invitamos a ver nuestro curso. Aprende, explora y disfruta el proceso. El conocimiento no se toma vacaciones.',
      pt: 'As férias também podem ser um bom momento para aprender no seu próprio ritmo. Se você quer continuar treinando sua mente e reforçando conceitos, convidamos você a assistir ao nosso curso. Aprenda, explore e aproveite o processo. O conhecimento não tira férias.',
    },
    button: {
      text: {
        en: 'OFMI Course',
        es: 'Curso OFMI',
        pt: 'Curso OFMI',
      },
      href: 'https://www.youtube.com/watch?v=eJThxTLg8QM&list=PLdSCJwXErQ8E2us6mFvv6rV_HLaowbqbC',
      target: '_blank',
    },
  },
  {
    image: '/media/homepage/copa.png',
    title: {
      en: 'Copa de Comunidades de Programación',
      es: 'Copa de Comunidades de Programación',
      pt: 'Copa de Comunidades de Programação',
    },
    description: {
      en: 'Congratulations to all the winners and thank you for participating! 💪 Keep practicing and get ready for the next edition! 🚀',
      es: '¡Felicitaciones a todos los ganadores y gracias por su participación! 💪 ¡Sigue practicando y prepárate para la próxima edición! 🚀',
      pt: 'Parabéns a todos os vencedores e obrigado pela participação! 💪 Continue praticando e prepare-se para a próxima edição! 🚀',
    },
    button: {
      text: {
        en: 'Final Ranklist',
        es: 'Ranklist final',
        pt: 'Ranklist final',
      },
      href: 'https://omegaup.github.io/CCP/',
      target: '_blank',
    },
  },
  {
    image: '/media/homepage/Instagram.png',
    title: {
      en: 'Do you already follow us on Instagram?',
      es: '¿Ya nos sigues en Instagram?',
      pt: 'Você já nos segue no Instagram?',
    },
    description: {
      en: 'Stay up to date with news, events, and much more. Follow us and be part of the community!',
      es: 'Entérate de noticias, eventos y mucho más. ¡Síguenos y sé parte de la comunidad!',
      pt: 'Fique por dentro das novidades, eventos e muito mais. Siga-nos e faça parte da comunidade!',
    },
    button: {
      text: {
        en: 'Follow us',
        es: 'Síguenos',
        pt: 'Siga-nos',
      },
      href: 'https://www.instagram.com/omegaup_org/',
      target: '_blank',
    },
  },
];

export default carouselConfig;
