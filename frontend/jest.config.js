module.exports = {
    moduleFileExtensions: ['ts', 'js', 'jsx'],
    transform: {
        "^.+\\.jsx?$": "babel-jest"
    },
    globals: {
      'ts-jest': {
        tsconfig: 'tsconfig.json',
      },
    },
    moduleNameMapper: {
    },
  }
  