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
    image: '/media/homepage/carousel_slide_1.svg',
    title: {
      en: 'Improve your programming skills',
      es: 'Mejora tus habilidades de programación',
      pt: 'Melhore suas habilidades de programação',
    },
    description: {
      en:
        'omegaUp is a free educational platform that helps you improve your programming skills, used by thousands of students and teachers in Latin America. Is this the first time you use omegaUp?',
      es:
        'omegaUp es una plataforma educativa gratuita que te ayuda a mejorar tus habilidades de programación, usada por decenas de miles de estudiantes y docentes en Latinoamérica. ¿Es la primera vez que usas omegaUp?',
      pt:
        'omegaUp é uma plataforma educacional gratuita que ajuda você a melhorar suas habilidades de programação, usada por milhares de estudantes e professores na América Latina. É a primeira vez que você usa o omegaUp?',
    },
    button: {
      text: {
        en: 'See the tutorial',
        es: 'Ve el tutorial',
        pt: 'Veja o tutorial',
      },
      href: 'https://blog.omegaup.com/documentation/introduccion-a-omegaup/',
      target: '_blank',
    },
  },
  {
    image: '/media/homepage/carousel_slide_2.svg',
    title: {
      en: 'Would you like to learn to program?',
      es: '¿Te gustaría aprender a programar?',
      pt: 'Deseja aprender a programar?',
    },
    description: {
      en:
        'Join the free Introduction to C++ course on omegaUp. It consists of lessons, examples and problems spread over 7 modules',
      es:
        'Apúntate al curso gratuito de Introducción a C++ en omegaUp. Consta de lecciones, ejemplos y problemas repartidos en 7 módulos.',
      pt:
        'Participe do curso gratuito de Introdução ao C ++ no omegaUp. Consiste em lições, exemplos e problemas espalhados por 7 módulos',
    },
    button: {
      text: {
        en: 'Enter here',
        es: 'Ingresa aquí',
        pt: 'Entre aqui',
      },
      href: '/course/introduccion_a_cpp/',
      target: '_self',
    },
  },
  {
    image: '/media/homepage/discord_logo.svg',
    title: {
      en: 'Join our coders community',
      es: 'Únete a nuestra comunidad de coders',
      pt: 'Junte-se à nossa comunidade de coders',
    },
    description: {
      en:
        'Join omegaUp´s Discord server and hang out with your community, get help and learn about new projects.',
      es:
        'Únete al servidor de Discord de omegaUp y convive con la comunidad, obtén ayuda y entérate de los nuevos proyectos.',
      pt:
        'Junte-se ao servidor do Discord do omegaUp e convive com a comunidade, obtenha ajuda e aprenda sobre novos projetos.',
    },
    button: {
      text: {
        en: 'Join here',
        es: 'Únete aquí',
        pt: 'Junte-se aqui',
      },
      href: 'https://discord.com/invite/K3JFd9d3wk',
      target: '_blank',
    },
  },
];

export default carouselConfig;
