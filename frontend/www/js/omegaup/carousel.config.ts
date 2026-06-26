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
    image: '/media/homepage/carousel_slide_4.svg',
    title: {
      en: 'Start preparing with Intro OFMI',
      es: 'Empieza a prepararte con Intro OFMI',
      pt: 'Comece a se preparar com o Intro OFMI',
    },
    description: {
      en: 'New to competitive programming? Take the Intro OFMI course and start building the skills needed to participate in the Olympiad.',
      es: '¿Eres nueva en la programación competitiva? Toma el curso Intro OFMI y comienza a desarrollar las habilidades necesarias para participar en la olimpiada.',
      pt: 'Nova na programação competitiva? Faça o curso Intro OFMI e comece a desenvolver as habilidades necessárias para participar da olimpíada.',
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
  {
    image: '/media/homepage/blog.svg',
    title: {
      en: 'Visit our blog',
      es: 'Visita nuestro blog',
      pt: 'Visite nosso blog',
    },
    description: {
      en: 'Discover tutorials, articles, and the latest news from the omegaUp community.',
      es: 'Descubre tutoriales, artículos y las últimas noticias de la comunidad de omegaUp.',
      pt: 'Descubra tutoriais, artigos e as últimas notícias da comunidade omegaUp.',
    },
    button: {
      text: {
        en: 'Go to Blog',
        es: 'Ir al blog',
        pt: 'Ir para o blog',
      },
      href: 'https://blog.omegaup.com/',
      target: '_blank',
    },
  },
  {
    image: '/media/homepage/carousel_slide_UX_experience.png',
    title: {
      en: 'omegaUp UX experience',
      es: 'Experiencia de usuario de omegaUp',
      pt: 'Experiência de usuário de omegaUp',
    },
    description: {
      en: 'Your opinion builds our community!\nAt omegaUp, our priority is you. We want to know what you love and what we can improve so that your learning experience is amazing.\nIt will only take 3 minutes.\nThanks for helping us grow!',
      es: '¡Tu opinión construye nuestra comunidad!\nEn omegaUp, nuestra prioridad eres tú. Queremos saber qué te encanta y qué podemos mejorar para que tu experiencia de aprendizaje sea increíble.\nSolo te tomará 3 minutos.\n¡Gracias por ayudarnos a crecer!',
      pt: 'Sua opinião constrói nossa comunidade!\nNa omegaUp, nossa prioridade é você. Queremos saber o que você ama e o que podemos melhorar para que sua experiência de aprendizado seja incrível.\nLevará apenas 3 minutos.\nObrigado por nos ajudar a crescer!',
    },
    button: {
      text: {
        en: 'Click here to answer',
        es: 'Haz clic aquí para responder',
        pt: 'Clique aqui para responder',
      },
      href: 'https://forms.gle/q8fi19Y2qtPU3uVt6',
      target: '_blank',
    },
  },
];

export default carouselConfig;
