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
  /*{
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
  },*/
  {
    image: '/media/homepage/egoi25.png',
    title: {
      en: 'Mexico present at EGOI 2025',
      es: 'México presente en la EGOI 2025',
      pt: 'México presente na EGOI 2025',
    },
    description: {
      en: 'We proudly celebrate our team’s participation at the European Girls’ Olympiad in Informatics (EGOI) 2025 in Bonn, Germany. 🎉🎉🎉 Thanks to our sponsors, donors, and volunteers for making this dream possible. 💙',
      es: 'Con orgullo celebramos la participación de nuestro equipo en la European Girls’ Olympiad in Informatics (EGOI) 2025 en Bonn, Alemania. 🎉🎉🎉 Gracias a patrocinadores, donantes y al voluntariado por hacer posible este sueño. 💙',
      pt: 'Com orgulho, celebramos a participação de nossa equipe na European Girls’ Olympiad in Informatics (EGOI) 2025 em Bonn, Alemanha. 🎉🎉🎉 Obrigado aos patrocinadores, doadores e voluntários por tornar este sonho possível. 💙',
    },
    button: {
      text: {
        en: 'EGOI',
        es: 'EGOI',
        pt: 'EGOI',
      },
      href: 'https://egoi.org/',
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
    image: '/media/homepage/voces_tech_intro.png',
    title: {
      en: 'Voces Tech: Inspiring Stories',
      es: 'Voces Tech: Historias que inspiran',
      pt: 'Voces Tech: Histórias inspiradoras',
    },
    description: {
      en:
        'Discover Voces Tech, our new YouTube series where leaders and innovators in technology share their experiences and insights to inspire your journey.',
      es:
        'Descubre Voces Tech, nuestra nueva serie de YouTube donde líderes e innovadores en tecnología comparten sus experiencias e ideas para inspirarte en tu camino.',
      pt:
        'Descubra o Voces Tech, nossa nova série do YouTube onde líderes e inovadores em tecnologia compartilham suas experiências e percepções para inspirar sua jornada.',
    },
    button: {
      text: {
        en: 'Watch playlist',
        es: 'Ver playlist',
        pt: 'Ver playlist',
      },
      href:
        'https://www.youtube.com/playlist?list=PLdSCJwXErQ8HDWYi63f9IMg_czC-zbM8L',
      target: '_blank',
    },
  },
];

export default carouselConfig;
