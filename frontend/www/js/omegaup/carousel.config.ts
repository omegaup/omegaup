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
  };
}[] = [
  {
    image: '/media/homepage/carousel_slide_1.svg',
    title: {
      en: 'Welcome to omegaUp',
      es: 'Bienvenido a omegaUp',
      pt: 'Bem-vindo a omegaUp',
    },
    description: {
      en: 'Is this the first time you use omegaUp?',
      es: '¿Es la primera vez que usas omegaUp?',
      pt: 'Esta é a primeira vez que você usa o omegaUp?',
    },
    button: {
      text: {
        en: 'See the tutorial',
        es: 'Ve el tutorial',
        pt: 'Veja o tutorial',
      },
      href: 'https://blog.omegaup.com/category/omegaup/omegaup-101/',
    },
  },
  {
    image: '/media/homepage/carousel_slide_2.svg',
    title: {
      en: 'Introductory Course to C++',
      es: 'Curso de Introducción a C++',
      pt: 'Curso Introdutório ao C++',
    },
    description: {
      en:
        'Would you like to learn to program? Join the Introduction to C++ course on omegaUp. It consists of lessons, examples and problems spread over 7 modules',
      es:
        '¿Te gustaría aprender a programar? Apúntate al curso de Introducción a C++ en omegaUp. Consta de lecciones, ejemplos y problemas repartidos en 7 módulos.',
      pt:
        'Deseja aprender a programar? Participe do curso Introdução ao C ++ no omegaUp. Consiste em lições, exemplos e problemas espalhados por 7 módulos',
    },
    button: {
      text: {
        en: 'Enter here',
        es: 'Ingresa aquí',
        pt: 'Entre aqui',
      },
      href: '/course/introduccion_a_cpp/',
    },
  },
];

export default carouselConfig;
