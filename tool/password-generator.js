// パスワード生成ツール・パスワード生成方法
// 入力：JSON
// {
//     pwclass: ["パスワード使用文字クラス１","パスワード使用文字クラス２",...,"パスワード使用文字クラスｎ"], （Array[String]）
//     pwlength: パスワード長（Number）
// }
// 入力制限：
// class, length ともに存在すること。
//   classに指定される「パスワード使用文字クラス」は、必ず１文字以上を記入した文字列であること。空配列も認めない。
//      パスワード使用文字クラスは、利用する文字そのものを指定し、256文字未満であること。
//   length の長さは、class.length 以上の大きさで指定されること。また、65536未満であること。
const random_max = Uint8Array.BYTES_PER_ELEMENT * 256 - 1;
const length_max = 65535;

class pwgenerator {
    constructor(crypto_module) {
        if (crypto_module === null || crypto_module === undefined) {
            if (window !== null && window.crypto !== null) {
                this.cryptomodule = window.crypto;
            } else {
                throw 'Crypto module is not defined.';
            }
        } else {
            this.cryptomodule = crypto_module;
        }
    }
    validate(input) {
        if (toString.call(input) !== '[object Object]') {
            return false;
        }
        if (toString.call(input.pwclass) !== '[object Array]') {
            return false;
        } else if (input.pwclass.length <= 0) {
            return false;
        } else {
            for (let letters of input.pwclass) {
                if (toString.call(letters) !== '[object String]' || letters.length < 1 || random_max < letters.length) {
                    return false;
                }
            }
        }
        if (toString.call(input.pwlength) !== '[object Number]' || input.pwlength < input.pwclass.length || length_max < input.pwlength) {
            return false;
        }
        return true;
    }
    calc_random(ubound){
        let count = Math.floor( random_max / ubound );
        let seed_max = ubound * count;
        let seed;
        do {
            // see https://developer.mozilla.org/ja/docs/Web/API/Crypto/getRandomValues
            seed = this.cryptomodule.getRandomValues(new Uint8Array(1))[0];
        } while (seed >= seed_max);
        return seed % ubound;
    }
    calc_random_letter(pwclass){
        return pwclass[this.calc_random(pwclass.length)];
    }
    generate_with_each_pwclass(pwclass, pwlength) {
        let result = "";
        for (let i = 0; i < pwlength; i++) {
            result = result + this.calc_random_letter(pwclass);
        }
        return result;
    }
    generate(input) {
        if (this.validate(input) === false) return null;

        // generate letters from input.pwclass
        let result_string = "";
        let letter_count = input.pwlength;
        for (let i = 0; i < input.pwclass.length - 1; i++) {
            let letters_to_calc = this.calc_random(letter_count - (input.pwclass.length - 1 - i)) + 1; // range : from 1 to (input.pwlength - input.pwclass.length + 1)
            result_string = result_string + this.generate_with_each_pwclass(input.pwclass[i], letters_to_calc);
            letter_count = letter_count - letters_to_calc;
        }
        result_string = result_string + this.generate_with_each_pwclass(input.pwclass[input.pwclass.length - 1], letter_count);

        // shuffle letters
        for (let j = 0; j < 10000; j++) {
            let charToLast = this.calc_random(input.pwlength - 1);
            result_string = result_string.substring(0, charToLast) + result_string.substring(charToLast + 1, input.pwlength) + result_string[charToLast]; 
        }

        return result_string;
    }
}
