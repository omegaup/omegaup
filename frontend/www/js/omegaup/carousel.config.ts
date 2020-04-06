const carouselConfig: {
  _meta?: string;
  comments?: string[];
  image?: string;
  title?: {
    en: string;
    es: string;
    pt: string;
  };
  description?: {
    en: string;
    es: string;
    pt: string;
  }
}[] = [
  {
      "_meta": "comments",
      "comments": [
          "Information to generate the homepage carousel.",
          "To add a new slide on the carousel, your need a key that identifies the new",
          "element, the file path of the image (relative to /media/) to add on the slide",
          "(imageName), a title and brief description in all supported languages."
      ]
  },
  {
      "image": "/media/homepage/carousel_slide_1.svg",
      "title": {
          "en": "Welcome",
          "es": "Bienvenido",
          "pt": "Bem-vindo"
      },
      "description": {
          "en": "omegaUp is an open source platform for learning and improving your Computer Science skills through coding challenges with a fun and competitive approach",
          "es": "omegaUp es una plataforma de código abierto para aprender y mejorar tus habilidades en Ciencias de la Computación a través de retos y competencias",
          "pt": "omegaUp é uma plataforma de código aberto para aprender e aprimorar suas habilidades em Ciência da Computação por meio de desafios de codificação com uma abordagem divertida e competitiva"
      }
  },
  {
      "image": "/media/homepage/carousel_slide_2.svg",
      "title": {
          "en": "omegaUp Schools",
          "es": "Escuelas en omegaUp",
          "pt": "Escolas em omegaUp"
      },
      "description": {
          "en": "Create your own courses with homework and exams and teach your students algorithmic thinking while having fun using omegaUp",
          "es": "Crea tus propios cursos con tareas y exámenes y enseña a tus estudiantes pensamiento algorítmico mientras se divierten utilizando omegaUp",
          "pt": "Crie seus próprios cursos com trabalhos de casa e exames e ensine aos alunos o pensamento algorítmico enquanto se diverte usando o omegaUp"
      }
  },
  {
      "image": "/media/homepage/carousel_slide_3.svg",
      "title": {
          "en": "Mentors",
          "es": "Mentores",
          "pt": "Mentores"
      },
      "description": {
          "en": "Create your own courses with homework and exams and teach your students algorithmic thinking while having fun using omegaUp",
          "es": "omegaUp ofrece a los coders del mes la posibilidad de recibir mentoría personalizada de ingenieros de software voluntarios con experiencia internacional que laboran en las principales empresas de tecnología del mundo",
          "pt": "omegaUp oferece aos codificadores do mês a oportunidade de receber orientação personalizada de engenheiros de software voluntários com experiência internacional que trabalham nas principais empresas de tecnologia do mundo"
      }
  }
];

export default carouselConfig;
