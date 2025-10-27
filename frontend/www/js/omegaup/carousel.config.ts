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
    image: '/media/homepage/egoi25.png',
    title: {
      en: 'Mexico present at EGOI 2025',
      es: 'México presente en la EGOI 2025',
      pt: 'México presente na EGOI 2025',
    },
    description: {
      en:
        'We proudly celebrate our team’s participation at the European Girls’ Olympiad in Informatics (EGOI) 2025 in Bonn, Germany. 🎉🎉🎉 Thanks to our sponsors, donors, and volunteers for making this dream possible. 💙',
      es:
        'Con orgullo celebramos la participación de nuestro equipo en la European Girls’ Olympiad in Informatics (EGOI) 2025 en Bonn, Alemania. 🎉🎉🎉 Gracias a patrocinadores, donantes y al voluntariado por hacer posible este sueño. 💙',
      pt:
        'Com orgulho, celebramos a participação de nossa equipe na European Girls’ Olympiad in Informatics (EGOI) 2025 em Bonn, Alemanha. 🎉🎉🎉 Obrigado aos patrocinadores, doadores e voluntários por tornar este sonho possível. 💙',
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
    image: '/media/homepage/copa.png',
    title: {
      en: 'Copa de Comunidades de Programación',
      es: 'Copa de Comunidades de Programación',
      pt: 'Copa de Comunidades de Programação',
    },
    description: {
      en:
        'Congratulations to all the winners and thank you for participating! 💪 Keep practicing and get ready for the next edition! 🚀',
      es:
        '¡Felicitaciones a todos los ganadores y gracias por su participación! 💪 ¡Sigue practicando y prepárate para la próxima edición! 🚀',
      pt:
        'Parabéns a todos os vencedores e obrigado pela participação! 💪 Continue praticando e prepare-se para a próxima edição! 🚀',
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
