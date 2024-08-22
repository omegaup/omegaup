const sugarMock = {
  extend: jest.fn(() => {}),
  Date: {
    addLocale: jest.fn(),
    getLocale: jest.fn(),
  },
};

module.exports = sugarMock;
