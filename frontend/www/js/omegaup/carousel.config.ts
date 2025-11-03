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
    image: '/media/homepage/instagram.png',
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
