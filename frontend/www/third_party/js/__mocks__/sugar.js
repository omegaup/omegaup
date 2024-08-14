const sugarMock = {
  extend: jest.fn(() => {}),
  Date: {
    addLocale: jest.fn(),
  },
};

module.exports = sugarMock;
