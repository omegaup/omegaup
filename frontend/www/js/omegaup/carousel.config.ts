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
    image: '/media/homepage/ofmi.png',
    title: {
      en: 'Registration is now open!',
      es: '¡Ya está abierta la convocatoria!',
      pt: 'Inscrições abertas!',
    },
    description: {
      en:
        'Discover, learn, and participate in the community that drives female talent in programming. 5th Mexican Female Informatics Olympiad (OFMI).',
      es:
        'Descubre, aprende y participa en la comunidad que impulsa el talento femenino en la programación. 5ª Olimpiada Femenil Mexicana de Informática (OFMI).',
      pt:
        'Descubra, aprenda e participe da comunidade que impulsiona o talento feminino na programação. 5ª Olimpíada Feminina Mexicana de Informática (OFMI).',
    },
    button: {
      text: {
        en: 'View call',
        es: 'Ver convocatoria',
        pt: 'Ver convocatória',
      },
      href:
        'https://ofmi.omegaup.com/convocatoria',
      target: '_blank',
    },
  },
  {
    image: '/media/homepage/carousel_slide_4.svg',
    title: {
      en: 'Start preparing with Intro OFMI',
      es: 'Empieza a prepararte con Intro OFMI',
      pt: 'Comece a se preparar com o Intro OFMI',
    },
    description: {
      en:
        'New to competitive programming? Take the Intro OFMI course and start building the skills needed to participate in the Olympiad.',
      es:
        '¿Eres nueva en la programación competitiva? Toma el curso Intro OFMI y comienza a desarrollar las habilidades necesarias para participar en la olimpiada.',
      pt:
        'Nova na programação competitiva? Faça o curso Intro OFMI e comece a desenvolver as habilidades necessárias para participar da olimpíada.',
    },
    button: {
      text: {
        en: 'Start course',
        es: 'Comenzar curso',
        pt: 'Começar curso',
      },
      href: 'https://omegaup.com/course/Intro-OFMI/',
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
      en:
        'Stay up to date with news, events, and much more. Follow us and be part of the community!',
      es:
        'Entérate de noticias, eventos y mucho más. ¡Síguenos y sé parte de la comunidad!',
      pt:
        'Fique por dentro das novidades, eventos e muito mais. Siga-nos e faça parte da comunidade!',
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
