const carouselConfig = [
  {
    '_meta': 'comments',
    'comments': [
      'Information to generate the homepage carousel.',
      'To add a new slide on the carousel, your need a key that identifies the new',
      'element, the file path of the image (relative to /media/) to add on the slide',
      '(imageName), a title and brief description in all supported languages.'
    ]
  },
  {
    'image': '/media/landing/carousel-slide-01.svg',
    'title': {'en': 'Welcome', 'es': 'Bienvenido', 'pt': 'Introdução'},
    'description': {
      'en':
          'omegaUp is an open source platform to learn and improve yourComputerScienceskillsthroughcodingchallengeswithafunandcompetitiveapproach',
      'es':
          'omegaUp es una plataforma libre para aprender y mejorar tus habilidades en Ciencias de la Computación a través de retos y competencias',
      'pt':
          'OmegaUp é uma plataforma gratuita para aprender e melhorarsuashabilidadesemCiênciadaComputaçãoatravésdedesafiosecompetências'
    }
  },
  {
    'image': '/media/landing/carousel-slide-02.svg',
    'title': {'en': 'Mentors', 'es': 'Mentores', 'pt': 'Mentores'},
    'description': {
      'en':
          'omegaUp will offer coders of the month the possibility of receivingpersonalizedmentoringbyvolunteersoftwareengineerswithinternationalexperience,havingworkedinprominenttechnologycompaniesintheworld',
      'es':
          'omegaUp ofrecerá a los coders del mes la posibilidad de recibir mentoría personalizada de ingenieros de software voluntarios con experiencia internacional, habiendolaborado en las principales empresas de tecnología del mundo',
      'pt':
          'omegaUp oferecerá codificadores do mês a possibilidade de receberAconselhamentopersonalizadodeengenheirosvoluntáriosdesoftwarecomexperiênciainternacional,tendotrabalhadonaprincipalempresasdetecnologianomundo'
    }
  },
  {
    'image': '/media/landing/carousel-slide-03.svg',
    'title': {
      'en': 'omegaUp Schools',
      'es': 'omegaUp Escuelas',
      'pt': 'omegaUp Escolas'
    },
    'description': {
      'en':
          'Create your own courses with homework and exams and teach yourstudentalgorithmicthinkingwhilehavingfunusingomegaUp',
      'es':
          'Crea tus propios cursos con tareas y exámenes y enseña a tus estudiantes pensamiento algorítmico mientras se divierten utilizando omegaUp',
      'pt':
          'Crie seus próprios cursos com lição de casa e exames e ensine seupensamentoalgorítmicoestudantilenquantosediverteusandoomegaUp'
    }
  },
];

export default carouselConfig;
